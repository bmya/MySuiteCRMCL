<?php

   // Basado en el fichero /download.php
   // Ver tambien la funcion 'DescargarInforme'
   
   // Si se usa desde el controller.php, hay que tener en cuenta lo siguiente
   //    $this->view = '';
   //    $GLOBALS['view'] = '';

   // Si se usa sin pasar por el controller.php (si el fichero se llamara download.php y la accion tambien se llama download) 
   // hay que desactivar en la vista activa el footer, el header, etc. de esta forma
   //    $this->options['show_header'] = false;
   //    $this->options['show_footer'] = false; ....


   if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

   if((empty($_REQUEST['record'])) || empty($_REQUEST['module']) || !isset($_SESSION['authenticated_user_id'])) {
      die("Not a Valid Call");
   }

   ini_set('zlib.output_compression','Off'); //bug 27089, if use gzip here, the Content-Length in header may be incorrect.
   
   // cn: bug 8753: current_user's preferred export charset not being honored
   $GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
   $GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
   $app_strings = return_application_language($GLOBALS['current_language']);
   $mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');
   
   $focus = SugarModule::get($_REQUEST['module'])->loadBean();
   if(!$focus->ACLAccess('view')){
      die($mod_strings['LBL_NO_ACCESS']);
   } 
   $focus->retrieve($_REQUEST['record']);  

   $download_location = '';
   if ( isset($focus->doc_local_location) && !empty($focus->doc_local_location) ) {
      //$download_location = getcwd() . "/". $focus->doc_local_location;  // no es necesario
      $download_location = $focus->doc_local_location;
   }

   if(!file_exists( $download_location ) || strpos($download_location, "..")) {
      die($app_strings['ERR_INVALID_FILE_REFERENCE']);
   } 

   $file_mime_type = $focus->file_mime_type;
   $name = basename($focus->filename);  //$name = $focus->document_name;

   if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
      $name = urlencode($name);
      $name = str_replace("+", "_", $name);
   }
   
   clearstatcache();
   $filesize = filesize($download_location);

   header("Pragma: public");
   header("Cache-Control: maxage=1, post-check=0, pre-check=0");
   //header("Content-Type: application/force-download");
   header("Content-type: application/octet-stream");
   header("Content-Disposition: attachment; filename=\"".$name."\";");
   // disable content type sniffing in MSIE
   header("X-Content-Type-Options: nosniff");
   header("Content-Length: " . $filesize);
   //header("Expires: 0");   
   header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
   set_time_limit(0);

   @ob_end_clean();
   ob_start();
   readfile($download_location);
   @ob_flush();

?>
