---metas---
title: Parsing de fichiers XML Simple et Rapide avec Fwk\Xml
slug: parsing-fichiers-xml-simple-rapide
category: fwk
excerp: Utilisation de Fwk\Xml pour le parsing de fichiers XML sans douleur.
---/metas---

Il est très fréquent pour un développeur web de devoir parser un fichier XML. Généralement en PHP, cette tâche se fait assez simplement à l'aide de [SimpleXML](http://php.net/simplexml). Or, le code à écrire est assez conséquent, souvent [spaghetti](http://fr.wikipedia.org/wiki/Programmation_spaghetti) et difficile à maintenir. Mais ça - comme dirait un célèbre opticien - c'était avant !

## Fwk\Xml

[Fwk\Xml](https://github.com/fwk/Xml) est un des composants de la suite Fwk qui permet de définir un schéma de parsing dans une classe dédiée (aka. une ```Map```). Une fois interprétée, cette Map retourne alors un tableau (```array```) des données récoltées. C'est tout !

Voici un example ou nous allons parser le flux RSS de ce blog, que nous aurons préalabelement sauvegardé dans ```/tmp/nitronet-rss.xml```. Son contenu est le suivant:

~~~
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
		<title>nitronet</title>
		<description>Le weblog de neiluJ</description>
		<link>http://nitronet.org</link>
		<item>
			<title>This is another test</title>
			<description>You think water moves fast? You should see ice. It moves
				like it has a mind. Like it knows it killed the world once and got a
				taste for murder. After the avalanche, it took us a week to climb
				out.</description>
			<link>http://nitronet.org/article/this-is-another-test</link>
			<pubDate>Wed, 07 Nov 2012 22:38:32 +0100</pubDate>
		</item>
		<item>
			<title>Hello World</title>
			<description>.</description>
			<link>http://nitronet.org/article/hello-world</link>
			<pubDate>Wed, 07 Nov 2012 16:02:54 +0100</pubDate>
		</item>
	</channel>
</rss>
~~~

Maintenant que nous connaissons la structure du fichier XML, nous allons créer une ```Map``` qui nous permettra d'en déduire les données importantes.

~~~
use Fwk\Xml\Map, Fwk\Xml\Path;

$rssMap = new Map();
$rssMap->->add(
    Path::factory('/rss/channel', 'channel')
    ->addChildren(Path::factory('title', 'title'))
    ->addChildren(Path::factory('link', 'link'))
    ->addChildren(Path::factory('description', 'description')
);
~~~

Un ```Path``` est une classe représentant un [Xpath](http://fr.wikipedia.org/wiki/XPath) dans lequels nous spécifions:
* Le chemin XML vers la donnée qui nous intéresse
* Le nom que nous souhaitons pour la clé dans notre tableau de résultats

Un ```Path``` peut également avoir plusieurs ```Path``` enfants. Ceux ci seront alors représentés dans un sous-tableau de notre tableau de résultats.

### First-run

Exécutons une première fois notre ```Map``` afin de voir déjà le résultat obtenu.

~~~
use Fwk\Xml\XmlFile;

$xml = new XmlFile('/tmp/nitronet-rss.xml');
$results = $rssMap->execute($xml);

print_r($results);
~~~

Résultat:

~~~
Array
(
    [channel] => Array
        (
            [title] => nitronet
            [link] => http://nitronet.org
            [description] => Le weblog de neiluJ
        )
)
~~~

### Loop

C'est bien gentil tout ça mais généralement lorsque l'on parse un flux RSS, c'est plutôt son contenu (ses *items*) que sa description qui nous intéresse. Nous allons donc enrichir notre Map afin d'avoir une entrée *items* dans notre résultat.

~~~
$rssMap->add(
    Path::factory('/rss/channel/item', 'items')
    ->loop(true)
    ->addChildren(Path::factory('title', 'title'))
    ->addChildren(Path::factory('link', 'link'))
    ->addChildren(Path::factory('pubDate', 'pubDate'))
    ->addChildren(Path::factory('description', 'description'))
);
~~~

Notez la définition du path en tant que boucle (```loop```). Cela signifie que nous attendons un sous-tableau de résultats pour chaque ligne (xml) correspondante. Voici le résultat final:

~~~
Array
(
    [channel] => Array
        (
            [title] => nitronet
            [link] => http://nitronet.org
            [description] => Le weblog de neiluJ
        )

    [items] => Array
        (
            [0] => Array
                (
                    [title] => This is another test
                    [link] => http://nitronet.org/article/this-is-another-test
                    [pubDate] => Wed, 07 Nov 2012 22:38:32 +0100
                    [description] => ...
                )

            [1] => Array
                (
                    [title] => Hello World
                    [link] => http://nitronet.org/article/hello-world
                    [pubDate] => Wed, 07 Nov 2012 16:02:54 +0100
                    [description] => ...
                )

        )
)
~~~

Nous avons maintenant un tableau complet, facile a parcourir en PHP pour traitement où affichage. Vous pouvez consulter [la classe créée pour l'occasion](https://github.com/fwk/Xml/blob/master/Maps/Rss.php) qui reprend l'intégralité de la ```Map``` pour les flux RSS.

D'autres articles viendront plus tard compléter l'overview de la façon dont s'utilise ```Fwk\Xml```. J'espère que dans vos tâches quotidiennes vous aurez l'occasion de le tester et pourquoi pas, contribuer au projet ? ;)
