<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
?>
<div id="about" title="<?php echo _MAARCH_CREDITS;?>" class="panel">
    <p id="logo" align="center">
    <img src="<?php
            echo $_SESSION['config']['businessappurl'];
        ?>static.php?filename=maarch_community.gif" alt="Maarch" />
    </p>
    <div class="wpb_wrapper">
            <p style="text-align:justify;">Nous sommes une société différente.<br>
Nous sommes une équipe d’ingénieurs et de consultants passionnés par l’Open Source.</p>
<p style="text-align:justify;">Nous éditons la plateforme Maarch, une application 100% Open Source de <strong>solutions professionnelles de dématérialisation des contenus</strong>, puissante, robuste et modulable.</p>
<p style="text-align:justify;">Nous développons Maarch depuis 10 ans avec notre communauté d’utilisateurs et d’experts.</p>
<p style="text-align:justify;">Maarch a su séduire de nombreuses administrations, départements et villes tels que le Ministère de l’Intérieur, la DGGN Gendarmerie Nationale, l’INPI, le Conseil Général de la Manche, la ville de Rouen… Maarch équipe&nbsp; l’ensemble des préfectures françaises.<br>
Maarch a aussi été adopté par de nombreuses entreprises telles que Numericable, Apria RSA, Miel Mutuelle…</p>
<p style="text-align:justify;"><strong>Maarch est présente en France, à Nanterre, mais aussi à Dakar au Sénégal avec Maarch West Africa.</strong></p>
<p style="text-align:justify;">La société s’appuie sur un réseau de partenaires installés sur l’ensemble du territoire métropolitain et outre-mer, en Europe en Afrique et aux Etats-Unis.</p>

        </div>
</div>
