<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SIPS - Backoffice</title>
<style type="text/css">

body label {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
}
body label.header {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 20px;
	color:#006;
}

body td.header {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 14px;
	color:#006;
}

body td {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 10px;
	text-align:center;
}
span{
cursor:pointer;
color:white;
background:#09F;
font-size:26px;
font:"Courier New", Courier, monospace
}
counter{
	font-size:24px;
}



input.bbuttons
{
   font-size:16px;
   width:80px;
   height:80px;
   white-space:normal;
}

input.abuttons
{
   background-color:#99CC66;
  
   width:130px;
   height:40px;
   border:2px;
   
	border-left: solid 2px #c3f83a;
	border-top: solid 2px #c3f83a;
	border-right: solid 2px #82a528;
	border-bottom: solid 2px #58701b;
}




textomaior {
	font-size: 16px;
}
textogrande {
	font-size: 16px;
}
textogrande {
	font-size: 18px;
}

</style>

<?php 

$pagina = $_POST['leads'];
$concelho = $_POST['concelho'];
$refinamento = $_POST['refinamento'];
$numpages = $_POST['numpages'];

$numpages = (int)$numpages;
$pagina = (int)$pagina;
$refinamento = (String)$refinamento;

$url = 'http://www.pbi.pai.pt/q/name/where/'.$concelho.'/';


//.$num_pages.'?refine='.$refinamento
//$strpages = (string)$num_pages;

//echo $url.$strpages;

?>


<script language ="javascript">

function boom(valor)
{
var url = valor;
var pn = <?php echo $numpages ?>;
var urlf = "";
var pagina = <?php echo $pagina ?>;
var paginafim = pagina+pn;
//var a = "?refine=locality1_";
//var b = "";
//b = <?php echo $refinamento; ?>;
//var refina =  a+b;
var refina = "";



for (var i=pagina;i<paginafim;i++)
{
	urlf = "";
	urlf = (url + i);
	urlf = urlf + "?refine=locality1_";
	urlf = urlf + "<?php echo $refinamento; ?>";
	window.open (urlf, i, "status=yes, menubar =yes, titlebar=yes, resizable=yes, scrollbars=yes");
	
}

 }
</script>
</head>

<body>
<table align="center" width="100%" border="1">
<tr valign="top">
    <td width="186">
       
          <iframe width="186" height="600" src="menu.html" frameborder="0" scrolling="no" hspace="0" marginheight="0" marginwidth="0">
    </iframe> 
        
   </td>
   <td width="100%" align="left">
   		<form method="post" target='_self'  name='lolpbi' action="pbi.php">

<label>pagina onde queres começar</label><input type="text" name='leads' /><br /><br />
<label>quantas paginas queres abrir?</label><input type="text" name='numpages' /><br /><br />
<label>concelho</label><input type="text" name='concelho' /><br /><br />
<label>refinamento</label><input type="text" name='refinamento' /><br /><br />

<br /><br /><br />
<input type="submit" value="enviar"/>
<br /><br /><br />


</form>
<input type="button" value="boom!" onclick=boom("<?php echo $url; ?>") />
</td>
</tr>
</table>

</body>
</html>