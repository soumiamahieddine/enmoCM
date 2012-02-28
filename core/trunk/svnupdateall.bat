cd core
echo "=" >> C:\xampp\htdocs\maarch_entreprise_trunk\update.log
echo date >> C:\xampp\htdocs\maarch_entreprise_trunk\update.log
echo time >> C:\xampp\htdocs\maarch_entreprise_trunk\update.log
svn update >> C:\xampp\htdocs\maarch_entreprise_trunk\update.log
cd ..\apps\maarch_entreprise
svn update >> C:\xampp\htdocs\maarch_entreprise_trunk\update.log
cd ..\..\modules
svn update *  >> C:\xampp\htdocs\maarch_entreprise_trunk\update.log
pause
