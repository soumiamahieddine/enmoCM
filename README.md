[![build status](https://labs.maarch.org/maarch/MaarchCourrier/badges/develop/build.svg)](https://labs.maarch.org/maarch/MaarchCourrier/commits/develop)
[![coverage report](https://labs.maarch.org/maarch/MaarchCourrier/badges/develop/coverage.svg)](https://labs.maarch.org/maarch/MaarchCourrier/commits/develop)



# Maarch Courrier
Gestionnaire Électronique de Correspondances – Libre et Open Source –

**Dernière version stable V18.10**

Démonstration : http://demo.maarchcourrier.com/

Documentation : https://docs.maarch.org/gitbook/html/MaarchCourrier/18.10/


## Installation
1. Vérifiez que vous avez l'ensemble des [pré-requis](https://docs.maarch.org/gitbook/html/MaarchCourrier/18.10/guat/guat_prerequisites/home.html)
2. Décompressez *MaarchCourrier-18.10.tar.gz* dans votre zone web
3. Vérifiez votre vhost Apache
4. Laissez-vous guider par notre installeur à [http://IP.ouDomaine.tld/MaarchCourrier/install/](https://docs.maarch.org/gitbook/html/MaarchCourrier/18.10/guat/guat_installation/online_install.html)


## Requis techniques

* Apache2.x
* PostgreSQL 9.x
* PHP 7.0.* ou PHP 7.1.*
   * Extensions PHP (adaptées à votre version de PHP) : PHP-[XSL](http://php.net/manual/en/book.xsl.php), PHP-[XML-RPC](http://php.net/manual/en/book.xmlrpc.php), PHP-[Gettext](http://php.net/manual/en/b$
   * Bibliothèques pear/CLITools
* [ImageMagick](http://imagemagick.org/), avec PHP-[ImageMagick](http://php.net/manual/en/book.imagick.php)
* [Ghostscript](https://www.ghostscript.com/)
* [7-zip](http://www.7-zip.org/)
* [wkhtmltopdf et wkhtmltoimage](http://wkhtmltopdf.org/downloads.html)
* [LibreOffice](http://libreoffice.org/) pour la conversion de documents
* [unoconv](https://packages.debian.org/jessie/unoconv) pour la conversion de documents
* Java Runtime Environment >= 7


###  Recommandations pour le php.ini

error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT  
display_errors = On  


## Le coin des developpeurs
[Maarch Developer handbook](https://labs.maarch.org/maarch/MaarchCourrier/blob/master/CONTRIBUTING.md)

