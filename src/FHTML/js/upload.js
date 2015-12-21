//upload functions
(function(FormHandler,$,undefined)
{
    var uploadHandlers = {};
    FormHandler.registerHandlerUploaded = function(field, callback)
    {
        uploadHandlers[field] = callback;
    }
    
    FormHandler.registerHandler(function(Form)
    {
        var StateEmpty = 1,
            StateErrorFileSize = 2,
            StateErrorTransmission = 3,
            StateErrorSystem = 4,
            StateUploading = 5,
            StateUploaded = 6,
            FileApiSupported = !!(window.File && window.FileReader && window.FileList && window.Blob),
            DragAndDropSupported = 'draggable' in document.createElement('span'),
            ProgressSupported = 'max' in document.createElement('progress'),
            humanFileSize = function(bytes, si)
            {
                var thresh = si ? 1000 : 1024;
                if(bytes < thresh) return bytes + ' B';
                var units = si ? ['KB','MB','GB','TB','PB','EB','ZB','YB'] : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
                var u = -1;
                do {
                    bytes /= thresh;
                    ++u;
                } while(bytes >= thresh);
                return bytes.toFixed(1)+' '+units[u];
            },
            changeState = function(Form, Field, State)
            {
                var FieldId = Field.attr('id'),
                    FieldButtonUpload = Form.dom.find('#'+ FieldId +'_button'),
                    FieldButtonChange = Form.dom.find('#'+ FieldId +'_change'),
                    FieldState = Form.dom.find('#'+ FieldId +'_state'),
                    DropZone = Form.dom.find('#'+ FieldId +'_dropzone'),
                    FieldStatus = Form.dom.find('#'+ FieldId +'_status');

                FieldState.val(State);

                if(State === StateEmpty)
                {
                    FieldButtonUpload.css('display', 'inline');
                    FieldButtonChange.css('display', 'none');
                    FieldStatus.text(DropZone.length !== 0 ? DropZone.data('drop-here') : FieldStatus.data('no-upload'));
                }
                if(State === StateUploading)
                {
                    FieldButtonUpload.css('display', 'inline');
                    FieldButtonChange.css('display', 'none');
                }
                if(State === StateUploaded)
                {
                    FieldButtonUpload.css('display', 'none');
                    FieldButtonChange.css('display', 'inline');
                }
                if(State === StateErrorFileSize
                    || State === StateErrorTransmission
                    || State === StateErrorSystem)
                {
                    //in cases of error revert to empty state
                    FieldButtonUpload.css('display', 'inline');
                    FieldButtonChange.css('display', 'none');
                    FieldStatus.text(FieldStatus.data('no-upload'));
                }
            };

        Form.dom.find('input[type=file]').each(function()
        {
            var fld = $(this),
                field_id = fld.attr('id'),
                box = $('div#FH_upload'),
                state = Form.dom.find('#'+ field_id +'_state'),
                filename = Form.dom.find('#'+ field_id +'_filename'),
                button = Form.dom.find('#'+ field_id +'_button'),
                token = Form.dom.find('#'+ field_id +'_token'),
                change = Form.dom.find('#'+ field_id +'_change'),
                status_field = Form.dom.find('#'+ field_id +'_status'),
                dropzone = Form.dom.find('#'+ field_id +'_dropzone'),
                max_upload = Form.dom.find('#MAX_FILE_SIZE'),
                form_id = Form.id +'_'+ field_id,
                frame_id = form_id + '_frame',
                hidden_frame = $('<iframe id="'+ frame_id +'" name="'+ frame_id +'" src="javascript:void(0);"></iframe>'),
                is_canceled = false,
                is_submitted = false,
                hidden_form = $('<form id="'+ form_id +'"></form>'),
                hidden_input = $('<input type="hidden" name="'+ Form.id +'_submit" value="1">'),
                hidden_input_field = $('<input type="hidden" name="'+ field_id +'_submit" value="1">'),
                submit_button = $('<button type="submit">submit</button>'),
                process_file_response = function(response)
                {
                    $('#'+ field_id +'_progress').remove();
                    try
                    {
                        var data = $.parseJSON(response);
                    }
                    catch(e)
                    {
                        var data = false;
                    }

                    if(typeof data === 'object')
                    {
                        if(data.state === StateUploaded)
                        {
                            status_field.text(FormHandler.truncateString(data.name));
                            filename.val(data.name);
                            
                            if(uploadHandlers.hasOwnProperty(field_id))
                            {
                                uploadHandlers[field_id](Form);
                            }
                        }
                        changeState(Form, fld, data.state);
                        is_canceled = false;
                        is_submitted = false;
                        return;
                    }

                    if(is_canceled === false)
                    {
                        changeState(Form, fld, StateErrorTransmission);
                        status_field.text(status_field.data('no-upload'));
                    }
                    is_canceled = false;
                    is_submitted = false;
                },
                process_uploading = function(result)
                {
                    var cancel = $('<a href="#" id="'+ field_id +'_cancel">Cancel</a>');
                    status_field
                        .text('Currently uploading: '+ FormHandler.truncateString(result) +' ')
                        .append(cancel);

                    changeState(Form, fld, StateUploading);
                    Form.disableButtons(true);
                },
                process_progress = function(e)
                {
                    if(ProgressSupported && e.lengthComputable)
                    {
                        $('#'+ field_id +'_progress').attr({value: e.loaded, max: e.total});
                    }
                },
                upload = function(file)
                {
                    var max_upload_size = parseInt(max_upload.val());
                    if(file.size > max_upload_size)
                    {
                        changeState(Form, fld, StateEmpty);
                        var too_large = dropzone.data('too-large') +"";
                        too_large = too_large.replace('%given%', humanFileSize(file.size));
                        too_large = too_large.replace('%allowed%', humanFileSize(max_upload_size));
                        status_field.text(too_large);
                        return;
                    }

                    if(ProgressSupported)
                    {
                        status_field.after($('<progress id="'+ field_id +'_progress" value="0" max="'+ file.size +'"></progress>'));
                    }

                    var data = new FormData();
                    data.append(Form.id +'_submit', 1);
                    data.append(field_id +'_submit', 1);
                    data.append(max_upload.attr('name'), max_upload_size);
                    data.append(field_id +'_token', token.val());
                    data.append(field_id, file);

                    process_uploading(file.name);

                    $.ajax({
                        url: Form.action,
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        type: 'POST',
                        xhr: function()
                        {
                            var myXhr = $.ajaxSettings.xhr();
                            if(myXhr.upload && ProgressSupported)
                            {
                                myXhr.upload.addEventListener('progress', process_progress, false);
                            }
                            return myXhr;
                        },
                        success: function(data)
                        {
                            Form.disableButtons(false);
                            process_file_response(data);
                        },
                        error: function(data)
                        {
                            Form.disableButtons(false);
                            $('#'+ field_id +'_progress').remove();
                            changeState(Form, fld, StateEmpty);
                        }
                    });
                };

            if(button.length === 0
                || state.length === 0
                || filename.length === 0
                || change.length === 0
                || token.length === 0
                || hidden_frame.get(0).attachEvent)
            {
                //continue when not correctly initialized
                //and when IE is found
                return;
            }

            if(box.length === 0)
            {
                //introduce hidden form placeholder
                var box = $('<div id="FH_upload" style="display:block;position:absolute;left:-2000px;top:-2000px;width:0;height:0;"></div>');
                $('body').append(box);
            }

            box.append(hidden_form);
            hidden_form.append(fld);

            hidden_form.append(hidden_input);
            hidden_form.append(hidden_input_field);

            if(max_upload.length !== 0)
            {
                hidden_form.append(max_upload.clone());
            }
            hidden_form.append(hidden_frame);
            hidden_form.append(token.clone());
            hidden_form.append(submit_button);

            if(dropzone.length !== 0 && FileApiSupported && DragAndDropSupported)
            {
                var dropZoneTimer;

                $(document).on('dragstart dragenter dragover', function(event)
                {
                    // Only file drag-n-drops allowed, http://jsfiddle.net/guYWx/16/
                    if($.inArray('Files', event.originalEvent.dataTransfer.types) !== 0)
                    {
                        return;
                    }

                    // Needed to allow effectAllowed, dropEffect to take effect
                    event.stopPropagation();
                    // Needed to allow effectAllowed, dropEffect to take effect
                    event.preventDefault();

                    clearTimeout(dropZoneTimer);
                    dropzone.addClass('dragover');
                    $('html').addClass('dragging');

                    if($('#dragging-layer').length == 0)
                    {
                        $('body').prepend('<div id="dragging-layer" class="overlay-layer"></div>');
                    }

                    // http://www.html5rocks.com/en/tutorials/dnd/basics/
                    // http://api.jquery.com/category/events/event-object/
                    event.originalEvent.dataTransfer.effectAllowed = 'none';
                    event.originalEvent.dataTransfer.dropEffect = 'none';

                    if($(event.target).hasClass('dragover')
                        || $(event.target).parents('.dragover').length !== 0)
                    {
                        event.originalEvent.dataTransfer.effectAllowed = 'copyMove';
                        event.originalEvent.dataTransfer.dropEffect = 'move';
                    }
                }).on('drop dragleave dragend', function()
                {
                    clearTimeout(dropZoneTimer);
                    dropZoneTimer= setTimeout( function()
                    {
                        dropzone.removeClass('dragover');
                        $('html').removeClass('dragging');
                        $('#dragging-layer').remove();
                    }, 70);
                    return false;
                });

                dropzone.addClass('FH_dropzoneDynamic');
                dropzone.on('drop', function(event)
                {
                    event.preventDefault();

                    upload(event.originalEvent.dataTransfer.files[0]);

                    clearTimeout(dropZoneTimer);
                    dropZoneTimer = setTimeout( function()
                    {
                        dropzone.removeClass('dragover');
                        $('html').removeClass('dragging');
                        $('#dragging-layer').remove();
                    }, 70);
                    return false;
                });

                fld.on('change', function()
                {
                    upload(this.files[0]);
                });
            }
            else
            {
                hidden_frame.on('load', function()
                {
                    Form.disableButtons(false);

                    if(is_submitted === false)
                    {
                        return;
                    }

                    var txt = $('#'+ frame_id).contents().text();
                    process_file_response(txt);
                });

                hidden_form.attr("action", Form.action);
                hidden_form.attr("method", "post");
                hidden_form.attr("enctype", "multipart/form-data");
                hidden_form.attr("accept-charset", Form.acceptCharset);
                hidden_form.attr("target", frame_id);
                hidden_form.attr("novalidate", 'novalidate');

                fld.on('change', function()
                {
                    var name = fld.val(),
                        result = (name.indexOf("\\") !== -1) ? name.substring(name.lastIndexOf("\\")+1) : name;
                        result = (result.indexOf("/") !== -1) ? result.substring(result.lastIndexOf("/")+1) : result;

                    if(result === '')
                    {
                        return;
                    }

                    process_uploading(result);

                    $(field_id +'_cancel').on('click', function()
                    {
                        Form.disableButtons(false);
                        is_canceled = true;

                        if(typeof window.frames[frame_id].stop === 'undefined')
                        {
                            //Internet Explorer code
                            window.frames[frame_id].document.execCommand('Stop');
                        }
                        else
                        {
                            //Other browsers
                            window.frames[frame_id].stop();
                        }
                        hidden_frame.attr('src','javascript:false');
                        fld.val('');

                        var current_state = filename.val();

                        if(current_state !== '')
                        {
                            status_field.text(FormHandler.truncateString(current_state));
                            changeState(Form, fld, StateUploaded);
                        }
                        else
                        {
                            changeState(Form, fld, StateEmpty);
                        }
                    });

                    is_submitted = true;

                    hidden_form.submit();
                });
            }

            //show field if hidden to fix old browser behavior
            fld.show();

            button.on('click', function()
            {
                fld.trigger('click');
            });
            change.on('click', function(event)
            {
                event.stopPropagation();
                fld.trigger('click');
                return false;
            });

            changeState(Form, fld, parseInt(state.val()));
        });
    });
}(window.FormHandler = window.FormHandler || {}, jQuery));