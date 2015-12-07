<table>
              <tr>
                <td valign="top"><div id="%%sFIELD_TITLE%%_ColorMap" style="width:256px;"></div></td>
                <td valign="top"><div id="%%sFIELD_TITLE%%_ColorBar"></div></td>
                <td valign="top">
	                <table>
	                    <tr>
	                      <td colspan="3"><div id="%%sFIELD_TITLE%%_Preview" style="background-color: #fff; width: 60px; height: 60px; padding: 0; margin: 0; border: solid 1px #000;"> <br />
	                      </div></td>
	                    </tr>
	                    <tr>
	                      <td><input type="radio" id="%%sFIELD_TITLE%%_HueRadio" name="%%sFIELD_TITLE%%_Mode" value="0" />
	                      </td>
	                      <td><label for="%%sFIELD_TITLE%%_HueRadio">H:</label>
	                      </td>
	                      <td><input type="text" id="%%sFIELD_TITLE%%_Hue" value="0" style="width: 40px;" />
	                        &deg; </td>
	                    </tr>
	                    <tr>
	                      <td><input type="radio" id="%%sFIELD_TITLE%%_SaturationRadio" name="%%sFIELD_TITLE%%_Mode" value="1" />
	                      </td>
	                      <td><label for="%%sFIELD_TITLE%%_SaturationRadio">S:</label>
	                      </td>
	                      <td><input type="text" id="%%sFIELD_TITLE%%_Saturation" value="100" style="width: 40px;" />
	                        % </td>
	                    </tr>
	                    <tr>
	                      <td><input type="radio" id="%%sFIELD_TITLE%%_BrightnessRadio" name="%%sFIELD_TITLE%%_Mode" value="2" />
	                      </td>
	                      <td><label for="%%sFIELD_TITLE%%_BrightnessRadio">B:</label>
	                      </td>
	                      <td><input type="text" id="%%sFIELD_TITLE%%_Brightness" value="100" style="width: 40px;" />
	                        % </td>
	                    </tr>
	                    <tr>
	                      <td colspan="3" height="5"></td>
	                    </tr>
	                    <tr>
	                      <td><input type="radio" id="%%sFIELD_TITLE%%_RedRadio" name="%%sFIELD_TITLE%%_Mode" value="r" />
	                      </td>
	                      <td><label for="%%sFIELD_TITLE%%_RedRadio">R:</label>
	                      </td>
	                      <td><input type="text" id="%%sFIELD_TITLE%%_Red" value="255" style="width: 40px;" />
	                      </td>
	                    </tr>
	                    <tr>
	                      <td><input type="radio" id="%%sFIELD_TITLE%%_GreenRadio" name="%%sFIELD_TITLE%%_Mode" value="g" />
	                      </td>
	                      <td><label for="%%sFIELD_TITLE%%_GreenRadio">G:</label>
	                      </td>
	                      <td><input type="text" id="%%sFIELD_TITLE%%_Green" value="0" style="width: 40px;" />
	                      </td>
	                    </tr>
	                    <tr>
	                      <td><input type="radio" id="%%sFIELD_TITLE%%_BlueRadio" name="%%sFIELD_TITLE%%_Mode" value="b" />
	                      </td>
	                      <td><label for="%%sFIELD_TITLE%%_BlueRadio">B:</label>
	                      </td>
	                      <td><input type="text" id="%%sFIELD_TITLE%%_Blue" value="0" style="width: 40px;" />
	                      </td>
	                    </tr>
	                    <tr>
	                      <td> #: </td>
	                      <td colspan="2"><input type="text" id="%%sFIELD_TITLE%%" name="%%sFIELD_TITLE%%" value="FF0000" style="width: 60px;" %%sEXTRA%% />
	                      </td>
	                    </tr>
	                </table>
	             </td>
              </tr>
            </table>
        <div style="display:none;">
		<img src="%%sFH_HTML%%images/ColorPicker/rangearrows.gif" />
		<img src="%%sFH_HTML%%images/ColorPicker/mappoint.gif" />

		<img src="%%sFH_HTML%%images/ColorPicker/bar-saturation.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-brightness.png" />

		<img src="%%sFH_HTML%%images/ColorPicker/bar-blue-tl.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-blue-tr.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-blue-bl.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-blue-br.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-red-tl.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-red-tr.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-red-bl.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-red-br.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-green-tl.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-green-tr.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-green-bl.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/bar-green-br.png" />

		<img src="%%sFH_HTML%%images/ColorPicker/map-red-max.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-red-min.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-green-max.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-green-min.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-blue-max.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-blue-min.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-saturation.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-saturation-overlay.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-brightness.png" />
		<img src="%%sFH_HTML%%images/ColorPicker/map-hue.png" />



	</div>
	<script type="text/javascript">

	Event.observe(window,'load',function() {
		%%sFIELD_TITLE%% = new Refresh.Web.ColorPicker('%%sFIELD_TITLE%%', {startHex: '%%sFIELD_VALUE%%', startMode:'s'});
	});

	
	</script>