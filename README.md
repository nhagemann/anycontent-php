# anycontent-php

getConfig/saveConfig am Repository

finalizeRecord ggf. noch plus Entfernen/Loggen aller unbekannten/unerlaubten properties?
finalizeConfig mit selbem Prinzip und hidden properties

check mandatory


userinfo soll nur im repository gesetzt werden und zugriff auf repository aus der connection??  (überdenken)
Testen custom config type record und generell handling überdenken (nur aus repository heraus?)

alpha ids



erneut check id = property? (u.a. wegen elastic)

S3 Files


Client-Klasse mit einfachem Zugriff auf Repositories und Setzen einer Cache-Strategie

Caching auf Repository-Ebene / repositoryInfo

anycontent-server-php

ancontent http client RestLikeBasicConnection, 
- Dimensions in die URLs inklusive Timeshift
- getConfig
- Caching CMDL muss aktiviert werden, Caching RepositoryInfo muss möglich sein

custom content type und stashing überprüfen
custom content type und caching überprüfen

RestLikeExtendedConnection


MySQLSchemalessConfiguration - setRepositoryName anstatt $repositoryName als Parameter


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
  
- VALIDATION - NOT PART OF THE STORAGE ENGINGE  