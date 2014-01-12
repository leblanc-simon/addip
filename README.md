AddIp
=====

Permet de mettre en place une interface web afin d'autoriser des adresses IP à se connecter à des serveurs.

On configure des droits dans le fichier de configuration et le site autorise les accès pour l'adresse IP se connectant sur le site.

Installation
------------

```bash
git clone https://github.com/leblanc-simon/addip.git
cc bin/addip.c -o bin/addip
cp config/config.ini.sample config/config.ini
```

Si le site est executé avec un autre utilisateur que l'utilisateur pouvant se connecter sur les serveurs distant :

```bash
cd bin
chown [user se connectant sur les serveurs distant] addip
chown [user se connectant sur les serveurs distant] addip.php
chmod 755 addip
chmod u+s addip
```

Il faut ensuite modifier le fichier config/config.ini  pour l'adapter à ses besoins :
* [command] : définition des binaires, temps d'autorisation, ...
* [port] : définition des types de ports disponibles
* [server] : définition des serveurs disponibles
* [user] : définition des utilisateurs et de leur mot de passe
* [right] : définition des droits d'accès

Astuces
-------

* Il est possible de personnaliser le message en fonction de l'utilisateur. Pour cela, il suffit de mettre un fichier [nom d'utilisateur].html dans le répertoire src/template. Celui-ci s'affichera alors en dessous de la confirmation d'autorisation.
* Faire pointer le DOCUMENT_ROOT sur www afin que le fichier de configuration ne soit pas accessible
