<?php
class OmnicoreRedir {

		var $adminOptionsName = "OmnicoreRedirAdminOptions";
		
		function OmnicoreRedir(){
		
		}
		
		function init(){
			global $wpdb,$isCookieSet;
			if (isset($_REQUEST['token'])){
				$sql = "SELECT deleted FROM ".$wpdb->prefix."OmnicoreRedir WHERE ID='" . $_REQUEST['token'] . "'";
				$deleted = $wpdb->get_var($sql);
				
				if ($deleted!=1){
					setcookie("OmnicoreRedir",$_REQUEST['token'],time()+3600*24*30);
					$this->DeleteToken($_REQUEST['token']);
					$isCookieSet=true;
				}
			}
			if (isset($_REQUEST['deletecookie'])){
				setcookie("OmnicoreRedir","",time() - 3600);
			}
		}
		
		function activate(){
			$this->createTables();
			$this->getAdminOptions();		
		}
		
		//Returns an array of admin options
		function getAdminOptions(){
			$OmnicoreRedirOptions = array('token_url' => 'http://',
				'secondary_url' => 'http://'
				);
			$devOptions = get_option($this->adminOptionsName);
			if (!empty($devOptions)){
				foreach($devOptions as $key => $option)
					$OmnicoreRedirOptions[$key]=$option;
			}
				update_option($this->adminOptionsName,$OmnicoreRedirOptions);
				return $OmnicoreRedirOptions;
		}	
		
		function deactivate(){
			$this->removeTables();
		}
		
		function createTables(){
			global $wpdb;
			
			$table = $wpdb->prefix."OmnicoreRedir";
			$structure = "CREATE TABLE IF NOT EXISTS $table (
			id varchar(13),
			deleted int(1)
			)";
			$wpdb->query($structure);	
		}
		
		function removeTables(){
			global $wpdb;
			
			$table = $wpdb->prefix."OmnicoreRedir";
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
			if (isset($_POST['update_OmnicoreRedirSettings'])){
				if (isset($_POST['OmnicoreRedirTokenUrl'])){
					$devOptions['token_url'] = $_POST['OmnicoreRedirTokenUrl'];
				}		
				if (isset($_POST['OmnicoreRedirSecondaryUrl'])){
					$devOptions['secondary_url'] = $_POST['OmnicoreRedirSecondaryUrl'];
				}	
				update_option($this->adminOptionsName,$devOptions);
				?>
<div class="updated"><p><strong><?php _e("Inställningarna uppdaterades.",'OmnicoreRedir');?></strong></p></div>
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
			<h2><?php _e("Omnicore Redirect administration",'OmnicoreRedir');?></h2>
			<h3><?php _e("Token inställningar.",'OmnicoreRedir');?></h3>
			<table class="form-table">
			<tr valign="top">
			<th scope="row">
			<label for="OmnicoreRedirTokenUrl"><?php _e("Token url",'OmnicoreRedir');?></label>
			<td><input type="text" name="OmnicoreRedirTokenUrl" value="<?php echo($devOptions['token_url']);?>">
			</td></tr>
			<tr valign="top">
			<th scope="row">
			<label for="OmnicoreRedirSecondaryUrl"><?php _e("Sekundär URL",'OmnicoreRedir');?></label>
			<td><input type="text" name="OmnicoreRedirSecondaryUrl" value="<?php echo($devOptions['secondary_url']);?>">
			</td></tr>						
			</table>
			<div class="submit">
			<input type="submit" name="update_OmnicoreRedirSettings" value="<?php _e('Uppdatera inställningar','OmnicoreRedir');?>"/></div>
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
				
				$sql = "SELECT deleted FROM ".$wpdb->prefix."OmnicoreRedir WHERE ID='" . $_REQUEST['token'] . "'";
				$deleted = $wpdb->get_var($sql);
				
				if (($isCookieSet==false)&&($deleted==1||($deleted==2&&$_COOKIE["OmnicoreRedir"]==""))){
					echo("<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=" . $devOptions['secondary_url'] . "\">");
					die();
				}else{
					//echo("<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=" . $devOptions['token_url'] . "\">");
				}
			}elseif (isset($_COOKIE["OmnicoreRedir"])&&$_COOKIE["OmnicoreRedir"]!=""){
				$sql = "SELECT deleted FROM ".$wpdb->prefix."OmnicoreRedir WHERE ID='" . $_COOKIE["OmnicoreRedir"] . "'";
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
			$sql = "SELECT * FROM ".$wpdb->prefix."OmnicoreRedir";
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
			$structure = "INSERT INTO ".$wpdb->prefix."OmnicoreRedir (id) VALUES ('" . uniqid() . "')";
			$wpdb->query($structure);	
		}
		function DeleteToken($id,$delete=false){
			global $wpdb;
			$deleted=($delete==false?2:1);
			$structure = "UPDATE ".$wpdb->prefix."OmnicoreRedir SET deleted=$deleted WHERE id='$id'";
			$wpdb->query($structure);	
			
		}
		
		function OmnicoreRedir_ap(){
			global $dl_pluginOmnicoreRedir;
			
			if (!isset($dl_pluginOmnicoreRedir)){
				return;
			}
			if (function_exists('add_options_page')){
				add_options_page('Omnicore Redir','Omnicore Redir',9,'printAdminPage',array(&$dl_pluginOmnicoreRedir,'printAdminPage'));
			}
		}
}
?>