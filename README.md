# anycontent-php


finalizeRecord ggf. noch plus Entfernen/Loggen aller unbekannten/unerlaubten properties?
finalizeConfig mit selbem Prinzip und hidden properties

check mandatory


alpha ids



erneut check id = property? (u.a. wegen elastic)

S3 Files


Client-Klasse mit einfachem Zugriff auf Repositories und Setzen einer Cache-Strategie

Caching auf Repository-Ebene / repositoryInfo

anycontent-server-php

ancontent http client RestLikeBasicConnection, 

- Caching RepositoryInfo muss möglich sein


custom content type und caching überprüfen

RestLikeExtendedConnection


MySQLSchemalessConfiguration - setRepositoryName anstatt $repositoryName als Parameter


AdminConnection
FilteringConnection -> Repository prüft Interface und überlässt Filterung der Connection
SortingConnection -> Repository prüft Interface und überlässt Sorting der Connection
NestedSortingConnection -> Repository prüft Interface und überlässt NestedSorting der Connection


Parser reduzieren
remove synchronize properties

Repository Constructor sollte repository name aufnehmen



MySQLOneToOne


- STASH
  Slow connections do stash records, configs or even all records during request
  
- VALIDATION - NOT PART OF THE STORAGE ENGINGE  