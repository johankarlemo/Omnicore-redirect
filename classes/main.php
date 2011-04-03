<?php
class KarlemoRedir {

		var $adminOptionsName = "KarlemoRedirAdminOptions";
		
		function KarlemoRedir(){
		
		}
		
		function init(){
			global $wpdb,$isCookieSet;
			if (isset($_REQUEST['token'])){
				$sql = "SELECT deleted FROM ".$wpdb->prefix."KarlemoRedir WHERE ID='" . $_REQUEST['token'] . "'";
				$deleted = $wpdb->get_var($sql);
				
				if ($deleted!=1){
					setcookie("KarLeMoRedir",$_REQUEST['token'],time()+3600*24*30);
					$this->DeleteToken($_REQUEST['token']);
					$isCookieSet=true;
				}
			}
			if (isset($_REQUEST['deletecookie'])){
				setcookie("KarLeMoRedir","",time() - 3600);
			}
		}
		
		function activate(){
			$this->createTables();
			$this->getAdminOptions();		
		}
		
		//Returns an array of admin options
		function getAdminOptions(){
			$KarlemoRedirOptions = array('token_url' => 'http://',
				'secondary_url' => 'http://'
				);
			$devOptions = get_option($this->adminOptionsName);
			if (!empty($devOptions)){
				foreach($devOptions as $key => $option)
					$KarlemoRedirOptions[$key]=$option;
			}
				update_option($this->adminOptionsName,$KarlemoRedirOptions);
				return $KarlemoRedirOptions;
		}	
		
		function deactivate(){
			$this->removeTables();
		}
		
		function createTables(){
			global $wpdb;
			
			$table = $wpdb->prefix."KarlemoRedir";
			$structure = "CREATE TABLE IF NOT EXISTS $table (
			id varchar(13),
			deleted int(1)
			)";
			$wpdb->query($structure);	
		}
		
		function removeTables(){
			global $wpdb;
			
			$table = $wpdb->prefix."KarlemoRedir";
			$structure = "DROP TABLE IF EXISTS $table";
			$wpdb->query($structure);	
		}
		
		function printAdminPage(){
		$devOptions = $this->getAdminOptions();
			//Saves the option values	
			if (isset($_REQUEST['action'])){			
				if ($_REQUEST['action']=='create'){
					$this->CreateToken();					
				}
				if ($_REQUEST['action']=='delete'){
					if ($_REQUEST['id']!=''){
						$this->DeleteToken($_REQUEST['id'],true);
					}
				}
			}
			if (isset($_POST['update_KarlemoRedirSettings'])){
				if (isset($_POST['KarlemoRedirTokenUrl'])){
					$devOptions['token_url'] = $_POST['KarlemoRedirTokenUrl'];
				}		
				if (isset($_POST['KarlemoRedirSecondaryUrl'])){
					$devOptions['secondary_url'] = $_POST['KarlemoRedirSecondaryUrl'];
				}	
				update_option($this->adminOptionsName,$devOptions);
				?>
<div class="updated"><p><strong><?php _e("Inställningarna uppdaterades.",'KarlemoRedir');?></strong></p></div>
				<?php
			}			
			if (!array_key_exists('token_url',$devOptions)){
				$devOptions['token_url'] = "http://";
			}	
			if (!array_key_exists('secondary_url',$devOptions)){
				$devOptions['secondary_url'] = "http://";
			}	
			?>
			<div class=wrap>
			<form method="post" action="<?php echo($_SERVER["REQUEST_URI"]); ?>">
			<h2><?php _e("KarLeMo Redirect administration",'KarlemoRedir');?></h2>
			<h3><?php _e("Token inställningar.",'KarlemoRedir');?></h3>
			<table class="form-table">
			<tr valign="top">
			<th scope="row">
			<label for="KarlemoRedirTokenUrl"><?php _e("Token url",'KarlemoRedir');?></label>
			<td><input type="text" name="KarlemoRedirTokenUrl" value="<?php echo($devOptions['token_url']);?>">
			</td></tr>
			<tr valign="top">
			<th scope="row">
			<label for="KarlemoRedirSecondaryUrl"><?php _e("Sekundär URL",'KarlemoRedir');?></label>
			<td><input type="text" name="KarlemoRedirSecondaryUrl" value="<?php echo($devOptions['secondary_url']);?>">
			</td></tr>						
			</table>
			<div class="submit">
			<input type="submit" name="update_KarlemoRedirSettings" value="<?php _e('Uppdatera inställningar','KarlemoRedir');?>"/></div>
			</form>
			<?php
				echo($this->ListTokens());
			?>
			<a href="<?php echo($_SERVER["REQUEST_URI"]); ?>&action=create">Skapa ny token</a>
			</div>
			<?php
		}
		function addHeaderCode(){
			global $wpdb,$isCookieSet;
			$devOptions = $this->getAdminOptions();
			if (isset($_REQUEST['token'])){
				
				$sql = "SELECT deleted FROM ".$wpdb->prefix."KarlemoRedir WHERE ID='" . $_REQUEST['token'] . "'";
				$deleted = $wpdb->get_var($sql);
				
				if (($isCookieSet==false)&&($deleted==1||($deleted==2&&$_COOKIE["KarLeMoRedir"]==""))){
					echo("<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=" . $devOptions['secondary_url'] . "\">");
					die();
				}else{
					//echo("<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=" . $devOptions['token_url'] . "\">");
				}
			}elseif (isset($_COOKIE["KarLeMoRedir"])&&$_COOKIE["KarLeMoRedir"]!=""){
				$sql = "SELECT deleted FROM ".$wpdb->prefix."KarlemoRedir WHERE ID='" . $_COOKIE["KarLeMoRedir"] . "'";
				$deleted = $wpdb->get_var($sql);
				if ($deleted==1){
					echo("<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=" . $devOptions['secondary_url'] . "\">"
					);
					die();
				}else{
					//echo("<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=" . $devOptions['token_url'] . "\">");
				}
			}else{
				echo("<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=" . $devOptions['secondary_url'] . "\">");
				die();
			}
		}
		function currentUrl(){
			$url ='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];				
			$url = array_shift(explode('?',$url,2));
			return $url;
		}
		
		function ListTokens(){
			global $wpdb;
			$sql = "SELECT * FROM ".$wpdb->prefix."KarlemoRedir";
			$tokens = $wpdb->get_results($sql);
			$return="<TABLE><tr><td>Token</td><td>Använd</td><td>Raderad</td><td>Radera</td></tr>";
			foreach($tokens as $token){
				$return.="<tr valign=\"top\">
				<td><a href=\"" . get_bloginfo('url') . "/?token=" . $token->id . "\">" . $token->id . "</a></td><td>" . ($token->deleted==2?'Ja':'Nej') . "</td><td>" . ($token->deleted==1?'Ja':'Nej') . "</td><td><a href=\"" . $_SERVER["REQUEST_URI"] ."&action=delete&id=" . $token->id . "\">Radera token</a></td></tr>";
			}
			$return.="</TABLE>";
			return $return;
		}
		function CreateToken(){
			global $wpdb;
			$structure = "INSERT INTO ".$wpdb->prefix."KarlemoRedir (id) VALUES ('" . uniqid() . "')";
			$wpdb->query($structure);	
		}
		function DeleteToken($id,$delete=false){
			global $wpdb;
			$deleted=($delete==false?2:1);
			$structure = "UPDATE ".$wpdb->prefix."KarlemoRedir SET deleted=$deleted WHERE id='$id'";
			$wpdb->query($structure);	
			
		}
		
		function KarlemoRedir_ap(){
			global $dl_pluginKarlemoRedir;
			
			if (!isset($dl_pluginKarlemoRedir)){
				return;
			}
			if (function_exists('add_options_page')){
				add_options_page('KarLeMo Redir','KarLeMo Redir',9,'printAdminPage',array(&$dl_pluginKarlemoRedir,'printAdminPage'));
			}
		}
}
?>