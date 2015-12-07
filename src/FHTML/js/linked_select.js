// create a new XHConn object which makes it possible to
// retrieve the content of a specific page
function XHConn()
{
  	var xmlhttp, bComplete = false;
  	try
  	{
  		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  	}
  	catch (e)
  	{
  		try
  		{
  			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  		}
  		catch (e)
  		{
  			try
  			{
  			    xmlhttp = new XMLHttpRequest();
  			}
  			catch (e)
  			{
  				xmlhttp = false;
  			}
  		}
  	}

  	if (!xmlhttp)
  	{
  		return null;
  	}

  	this.connect = function(sURL, sMethod, sVars, fnDone, buffer, fnAttach, fnArgs)
  	{
    	if (!xmlhttp)
    	{
    		return false;
    	}
    	bComplete = false;
    	sMethod = sMethod.toUpperCase();

    	try
    	{
      		if (sMethod == "GET")
      		{
        		xmlhttp.open(sMethod, sURL+"?"+sVars, true);
        		sVars = "";
      		}
      		else
      		{
        		xmlhttp.open(sMethod, sURL, true);
        		xmlhttp.setRequestHeader("Method", "POST "+sURL+" HTTP/1.1");
        		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  			}

  			xmlhttp.onreadystatechange = function()
  			{
  				if (xmlhttp.readyState == 4 && !bComplete)
  				{
          			bComplete = true;
          			fnDone(xmlhttp, buffer, fnAttach, fnArgs);
        		}
  			};
      		xmlhttp.send(sVars);
    	}
    	catch(z)
    	{
    		return false;
    	}

    	return true;
  	};
  	return this;
}

// get a specific element on the current page
function GetElement( id )
{
	result = document.getElementById ? document.getElementById(id): document.all? document.all[id]: null;

	if( !result )
	{
	    id += '[]';
	    return document.getElementById ? document.getElementById(id): document.all? document.all[id]: null;
	}
	else
	{
	    return result;
	}
}

// display the contents which is retrieved from the document
function displayExternal(oXML, buffer, fnAttach, fnArgs )
{
	lyr = GetElement( buffer );

	// get the value for this field
	if( typeof( fnArgs ) && fnArgs.constructor == Array && fnArgs.length > 0 )
	{
	   value = fnArgs.shift();
	}
	else
	{
	    value = null;
	}

	if( lyr )
	{
		var msg = null;
		try
		{
			// any text received ?
			if( oXML.responseText != "" )
			{
				// eval the js code
				eval( oXML.responseText );

				if( options != null )
				{
					loadOptions( lyr, options, value );
				}
			}
		}
		catch( q )
		{

			msg =
			"Could not load dynamic values!\n"+
			"Error: " + q.description + "\n" +
			"Received data: \n" +
			"----------------------------\n" +
			oXML.responseText;
		};

		if( msg )
		{
			alert( msg );
		}

		// run the function given by the user (if given)
	    if ( fnAttach )
	    {
	    	fnAttach( fnArgs );
	    }
	}
}

// set the new options in the field
function loadOptions( oFld, aOptions, sValue )
{
	// remove all current options of the selectfield
	oFld.options.length = 0;

	// add the new options
	len = 0;
	for( i = 0; i < aOptions.length; i++ )
	{
		elem = aOptions[i];

		if( typeof(elem) == "string" )
		{
			oFld.options[len] = new Option( elem );
			oFld.options[len].value = elem;
			if( elem == sValue )
			{
			    oFld.options[len].selected = true;
			}
		}
		else if( typeof( elem ) && elem.constructor == Array && elem.length >= 2 )
		{
			oFld.options[len] = new Option( elem[1] );
			oFld.options[len].value = elem[0];
			if( elem[0] == sValue )
			{
			    oFld.options[len].selected = true;
			}
		}
		else
		{
			//alert( elem );
		}
		len++;
	}
}

// load the given file with the given params
// filename: the name of the file which we should load
// params: the params we should give to the file
// buffer: the name of the buffer object (like a div) which should contain the retrieved contents of the loaded page
// fnAttach: name of a function we should run after the contents is retrieved (can be set to null of none)
// fnArgs: array of extra arguments which we should pass to the fnAttach function
function loadexternal(filename, params, buffer, fnAttach, fnArgs)
{
    var myConn = new XHConn();

    if (!myConn)
    {
    	alert("XMLHTTP not available. Try a newer/better browser.");
    }
    else
    {
    	myConn.connect(filename, "POST", params, displayExternal, buffer, fnAttach, fnArgs);
    }
}

// attach an event to an element
function attachelement(id, event, func)
{
	el = GetElement( id );

	// Mozilla, Netscape, Firefox
	if(window.addEventListener)
	{
		el.addEventListener(event, func, false);
	}
	// IE
	else
	{
		el.attachEvent("on"+event, func);
	}
}