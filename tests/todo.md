- finalizeRecord muss unerlaubte Properties entfernen und Warnings loggen
- finalizeConfig mit selbem Prinzip und hidden properties
- check mandatory
- alpha ids
- erneut check id = property? (u.a. wegen elastic)
- S3 Files
- Caching LastModified/RepositoryInfo für RestLike
- AdminConnection
- FilteringConnection -> Repository prüft Interface und überlässt Filterung der Connection
- SortingConnection -> Repository prüft Interface und überlässt Sorting der Connection
- NestedSortingConnection -> Repository prüft Interface und überlässt NestedSorting der Connection
- silex based anycontent-server-php to serve any type of repository
- Parser reduzieren / remove synchronize properties
- MySQLOneToOne
- RestLikeExtendedConnection with custom queries
- MySQLCache
- ParensParser
- CachingRepository: ForwardCaching
- CachingRepository: ConfigCaching
- CachingRepository: FilesCaching
- CachingRepository: respect confidence value
- CachingRepository: check only for cmdl modification

- Decision: Property VALIDATION - NOT PART OF THE STORAGE ENGINGE  