# anycontent-php



finalizeRecord ggf. noch plus Entfernen/Loggen aller unbekannten/unerlaubten properties?

check mandatory

content-type title überall entfernen

userinfo soll nur im repository gesetzt werden und zugriff auf repository aus der connection??  (überdenken)

cmdl folder bei MySQLSchemaLess als Alternative

Testen custom config type record und generell handling überdenken (nur aus repository heraus?)

Files

Client-Klasse mit einfachem Zugriff auf Repositories und Setzen einer Cache-Strategie

Caching auf Repository-Ebene / repositoryInfo

anycontent-server-php

AdminConnection
FilteringConnection -> Repository prüft Interface und überlässt Filterung der Connection
SortingConnection -> Repository prüft Interface und überlässt Sorting der Connection
NestedSortingConnection -> Repository prüft Interface und überlässt NestedSorting der Connection


Erweiterung Filter
- (string)für Filter wandelt in SimpleQuery-Format oder gleich Parenthesis

Parser reduzieren
remove synchronize properties

Repository Constructor sollte repository name aufnehmen






- STASH
  Slow connections do stash records, configs or even all records during request
  