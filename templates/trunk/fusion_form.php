
<form id="form1" name="form1" method="post" action="<?php 
    echo $_SESSION['config']['businessappurl'];
    ?>index.php?page=fusion_docx&module=templates&display">
    Enter a name:
    <input name="yourname" type="text" id="yourname" size="10" />, choose a template:
  <select name="tpl" id="tpl">
    <option value="demo_ms_word.docx">Ms Word Document (.docx)</option>
  </select>
  ,  debug:
  <select name="debug" id="debug">
    <option value="0" selected="selected">No</option>
    <option value="1">General Information</option>
    <option value="2">During merge</option>
    <option value="3">After merge</option>
  </select>
  <div id="save_as_file" style="display:none;">, save as file with suffix: 
    <input name="suffix" type="text" id="suffix" size="10" />
  (let empty for download),</div>
  <input type="submit" name="btn_go" id="btn_go" value="and go" />
</form>
