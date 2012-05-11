<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Language" content="<?php echo $Class_Install->getActualLang(); ?>" />
        <title>Maarch > <?php echo $longTitle; ?></title>
        <link rel="stylesheet" href="css/merged_css.css" />
        <script src="js/merged_js.js"></script>
    </head>

    <body onLoad="minHeightOfSection();heightOfLicenceOverflow();" onResize="minHeightOfSection();heightOfLicenceOverflow();">
        <div align="center">
            <div id="fullWrapper" class="fullWrapper">
                <header id="header">
                    <?php include('install/view/includes/header.php'); ?>
                </header>
                <div class="line"></div>
                <section id="section">
                    <br />
                    <?php include('install/view/includes/progress.php'); ?>
                    <br />
                    <?php include('install/view/'.$view.'_view.php'); ?>
                    <br />
                </section>
                <!-- <div class="line"></div> -->
                <!-- <footer id="footer">
                    <?php include('install/view/includes/footer.php'); ?>
                </footer> -->
            </div>
        </div>
    </body>
</html>
