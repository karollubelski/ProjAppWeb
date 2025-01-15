from dependency_injector.containers import DeclarativeContainer
from dependency_injector.providers import Factory, Singleton

from movieapp.infrastructure.repositories.moviedb import MovieRepository 
from movieapp.infrastructure.repositories.user import UserRepository
from movieapp.infrastructure.repositories.watcheddb import WatchedRepository
from movieapp.infrastructure.repositories.towatchdb import ToWatchRepository


from movieapp.infrastructure.services.movie import MovieService
from movieapp.infrastructure.services.user import UserService 
from movieapp.infrastructure.services.watched import WatchedService
from movieapp.infrastructure.services.towatch import ToWatchService



class Container(DeclarativeContainer):



    movie_repository = Singleton(MovieRepository)
    user_repository = Singleton(UserRepository) 
    watched_repository = Singleton(WatchedRepository)
    towatch_repository = Singleton(ToWatchRepository) 



    movie_service = Factory( 

        MovieService, 
        repository=movie_repository,  

    )

    user_service = Factory(
        UserService, 
        repository=user_repository, 

    )

    watched_service = Factory( 

        WatchedService,
        repository=watched_repository,

    )

    towatch_service = Factory( 

        ToWatchService, 
        repository=towatch_repository,


    )