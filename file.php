<?php
    ini_set('display_errors', 1);
    include_once(CGI_AUTOLOAD);


    
    if (!Portal_CGI::esta_logado()) {
    //	$layout->bloco('login');
    	die();
    }

    Portal_CGI::trava(TRUE);
    Portal_CGI::variaveis_legadas();
    $ob_user = Portal_CGI::ob_user();

    $WebPath = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR ;
    include_once($WebPath . 'includes/PHP/fn_layout.php'); // Inclui arquivo de layout
    include_once($WebPath . 'includes/CLASSES/class.GIPDO.php');
    include_once($WebPath . 'trava.php');
    include_once('config_upload.php');

    // Este é o vetor padrão de configurações do DAP
    $wf_cfg = array('database' => 'qualistore_form');  
    $tentativa_conexao_dap = 0;

    while( !( isset($pdo) && is_object($pdo) && $tentativa_conexao_dap < 5   ) ){
      sleep($tentativa_conexao_dap * 1);
        $pdo = new GIPDO(array(
          'dbname' => $wf_cfg['database']
        ));
    }

    $get_cfg = $pdo->prepare("SELECT US.LANG FROM gi_base.fx_usuarios US WHERE ID_USUARIO = ? ");
    $get_cfg->execute(array($ob_user->ID_USUARIO));
    $get_agente	= $get_cfg->fetch();
    $lang = $get_agente->LANG;

    if (trim($lang)) {
      require_once("../assets/lang/" . $lang . ".php");
    } else {
      require_once("../assets/lang/pt_br.php");
    }

    if (!acesso_upload_permitido()) {
        die($dp_lang->GEST_UP1);
    }

    $layout = new CGI_LAYOUT; // Chama e Classe

    $titulo = "Upload de base"; // Variável para sobrescrever título padrão (opcional) - Caso não declarado aparece "Portal CGI". Também é usado no <title> da página.
// $subtitulo	 = "Workforce Resgate - movél"; // Variável para definir um subtítulo - Caso não ceclarado, exibe somente o títu//lo

    $layout->html_start(); // Inicia HTML
    $layout->head_start(); // Abre <head>
    echo '<meta http-equiv="X-UA-Compatible" content="IE=Edge" />';
    // echo "<meta charset=\"UTF-8\">";
    echo "<script> cgi_header_fixed = false; </script>";
    $layout->head(); // Inclui scripts do layout (inclusive jquery 1.7
    $layout->load_script('cgi-jqueryui', true);
    $layout->load_script('cgicons', true);
?>
<style>
html body {
	background: #FFF;
}
</style>

<?php
    $layout->head_end(); // Fecha </head>
    $layout->body_start(); // Abre <body>
    //$layout->header();
    //$layout->page_wrap_start();
?>

<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/img/font/atento-omnes/atento-omnes.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          keyframes: {
            wiggle: {
              '0%' : { transform: 'translate(0, 0) rotate(0deg)'},
              '25%' : { transform: 'translate(5px, 5px) rotate(5deg)' },
              '50%' : { transform: 'translate(0, 0) rotate(0eg)' },
              '75%' : { transform: 'translate(-5px, 5px) rotate(-5deg)' },
              '100%' : { transform: 'translate(0, 0) rotate(0deg)' },
            }
          },
          animation: {
            wiggle: 'wiggle 0.35s infinite',
          },
          colors: {
            primary: '#00548c',
          }
        }
      }
    }
  </script>
  <title>Upload - Time to Talk</title>
  <style>
    #lan {
      -webkit-appearance: none;
      -moz-appearance: none;
      text-indent: 1px;
      text-overflow: '';
    }
    a.cgi-upload-container {
       background: white !important;
       width: 380px !important;
       color: black !important;
       border: dashed !important;
       font-weight: 200 !important;
       text-shadow: 0 0 black !important;
       margin-top: 75px !important;
    }
  </style>
</head>
<header class="bg-primary shadow-lg ">
  <a id="logoff" class="bg-red-600 hover:bg-red-400 text-white font-bold rounded absolute right-4 top-4 p-2">
    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V256c0 17.7 14.3 32 32 32s32-14.3 32-32V32zM143.5 120.6c13.6-11.3 15.4-31.5 4.1-45.1s-31.5-15.4-45.1-4.1C49.7 115.4 16 181.8 16 256c0 132.5 107.5 240 240 240s240-107.5 240-240c0-74.2-33.8-140.6-86.6-184.6c-13.6-11.3-33.8-9.4-45.1 4.1s-9.4 33.8 4.1 45.1c38.9 32.3 63.5 81 63.5 135.4c0 97.2-78.8 176-176 176s-176-78.8-176-176c0-54.4 24.7-103.1 63.5-135.4z"/></svg>
  </a>
  <?php if ($ob_user->ID_NIVEL == 1766 || $ob_user->ID_NIVEL == 1) { //?> 
  <a href="../portal.php" id="return" class="bg-yellow-500 hover:bg-yellow-300 text-white font-bold rounded absolute right-16 top-4 p-2">
    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M48.5 224H40c-13.3 0-24-10.7-24-24V72c0-9.7 5.8-18.5 14.8-22.2s19.3-1.7 26.2 5.2L98.6 96.6c87.6-86.5 228.7-86.2 315.8 1c87.5 87.5 87.5 229.3 0 316.8s-229.3 87.5-316.8 0c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0c62.5 62.5 163.8 62.5 226.3 0s62.5-163.8 0-226.3c-62.2-62.2-162.7-62.5-225.3-1L185 183c6.9 6.9 8.9 17.2 5.2 26.2s-12.5 14.8-22.2 14.8H48.5z"/></svg>
  </a>
  <?php } ?>
  <nav class="mx-auto flex items-center justify-between p-6 lg:px-8" aria-label="Global">
    <div class="select-none mx-8">
      <img src="../assets/img/LogoTTT_fondo_azul.png" alt="Logo" style="object-fit: contain; height: 230px; width: 230px;">
    </div>
    <div class="lg:flex-1 select-none">
      <div class="flex flex-col -m-1.5 p-1.5">
        <span class="sr-only select-none">Atento</span>
        <h1 class="text-white font-semibold text-5xl"><center><?= $dp_lang->TITULO; ?></center></h1>  
		    
		    <br>
        <p class="text-white text-base text-center font-semibold hidden md:inline-block mx-auto w-3/5"><justify><?= $dp_lang->TEXTO_53; ?></justify></p>
      </div>
    </div>
    <div id='lang_padre' class="flex flex-col items-center">
      <label for="lan" class="block text-lg font-medium leading-6 text-white"><?= $dp_lang->TEXTO_31; ?></label>
      <div class="flex">
        <a class="lan cursor-pointer">
          <img data-value="en_US" src="../assets/img/lang/en.png" alt="Logo" style="object-fit: contain; height: 36px; width: 36px;">
        </a>
        <a class="lan cursor-pointer">
          <img data-value="es_CO" src="../assets/img/lang/es.png" alt="Logo" style="object-fit: contain; height: 36px; width: 36px;">
        </a>
        <a class="lan cursor-pointer">
          <img data-value="pt_BR" src="../assets/img/lang/pt.png" alt="Logo" style="object-fit: contain; height: 36px; width: 36px;">
        </a>
      </div>
    </div>
  </nav>

</header>
<body>
    <div style="padding-top: 10px;">   
        <form method="POST"  enctype="multipart/form-data" class="align-center" action="upando.php">
            <input type="file" class="" name="upload" id="upload1" accept="<?= $cfg_upload['extensoes_permitidas'] ?>" /><br />   
            <button type="submit" style="border-radius: 5px;"class="rounded-md transition-colors bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 hover:bg-gray-900 hover:text-gray-100">
                <i class="cgicons cgicons-cloud-check"></i> 
                Subir
            </button>
    		<a href="<?php echo $cfg_upload['modelo']; ?>" id="link_modelo" class=" rounded-md transition-colors bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-900 hover:text-gray-100" style="border-radius: 5px;" ><i class="cgicons cgicons-cloud-download"></i>
                Descargar plantilla
            </a>
        </form>
    </div>
</body>
</html>

<?php
    $layout->load_css('/layout/plugins/cgi_upload/jquery.cgi_upload.css', true);
    $layout->load_js('/layout/plugins/cgi_upload/jquery.cgi_upload.js', true);
?>
<script>
    $("#upload1").cgi_upload({
        label: "Cargar base",
        width: 380
    });

</script>
<script src="../assets/js/upload.js"></script>
<style>/*.bt_envia_arquivo { display: none; }*/ </style>   
<?php
    //$layout->page_wrap_end();
    //$layout->footer(); // Insere rodapé
    $layout->body_end(); // Fecha </body>
    $layout->html_end(); // Fecha </html>	
	
