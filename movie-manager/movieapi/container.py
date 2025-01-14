from dependency_injector.containers import DeclarativeContainer
from dependency_injector.providers import Factory, Singleton

from movieapi.infrastructure.repositories.movie import MovieRepository
from movieapi.infrastructure.repositories.streamingdb import StreamingRepository
from movieapi.infrastructure.repositories.user import UserRepository
from movieapi.infrastructure.repositories.watcheddb import WatchedRepository
from movieapi.infrastructure.repositories.towatchdb import ToWatchRepository

from movieapi.infrastructure.services.movie import MovieService
from movieapi.infrastructure.services.streaming import StreamingService
from movieapi.infrastructure.services.user import UserService
from movieapi.infrastructure.services.watched import WatchedService
from movieapi.infrastructure.services.towatch import ToWatchService



class Container(DeclarativeContainer):

    streaming_repository = Singleton(StreamingRepository)
    user_repository = Singleton(UserRepository)
    movie_repository = Singleton(MovieRepository)
    watched_repository = Singleton(WatchedRepository)
    towatch_repository = Singleton(ToWatchRepository)

    streaming_service = Factory(
        StreamingService,
        repository=streaming_repository,
    )

    user_service = Factory(
        UserService,
        repository=user_repository,
    )
    movie_service = Factory(
        MovieService,
        repository=movie_repository,
    )
    watched_service = Factory(
        WatchedService,
        repository=watched_repository,
    )
    towatch_service = Factory(
        ToWatchService,
        repository=towatch_repository,
    )