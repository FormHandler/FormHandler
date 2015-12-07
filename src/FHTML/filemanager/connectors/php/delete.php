<script>
alert("delete disabled, take care of security!\n edit the delete.php");
</script>
<?

session_start();
if( isset( $_SESSION['login'] ) )
{
	// delete van een file voor FCKEditor
	// 08-03-2005 J.Wiegel @ PHP-GLOBE
	if( isset($_GET['path']))
	{
		unlink($_SERVER['DOCUMENT_ROOT'].$_GET['path']);
		?>
		<script>
		window.parent.frames["frmResourcesList"].Refresh();
		self.location.href = "../../browser/default/frmcreatefolder.html";
		</script>
		<?
	}
}
?>
