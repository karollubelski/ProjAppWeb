from typing import Iterable

from movieapi.core.domain.watched import Watched, WatchedIn
from movieapi.core.repositories.iwatched import IWatchedRepository
from movieapi.infrastructure.services.iwatched import IWatchedService


class WatchedService(IWatchedService):

    _repository: IWatchedRepository

    def __init__(self, repository: IWatchedRepository) -> None:
        self._repository = repository


    async def get_watched_by_id(self, watched_id: int) -> Watched | None:
        return await self._repository.get_watched_by_id(watched_id)

    async def get_all_watcheds(self) -> Iterable[Watched]:
        return await self._repository.get_all_watcheds()


    async def get_watched_by_movie(self, movie_title: str) -> Iterable[Watched]:
        return await self._repository.get_watched_by_movie(movie_title)

    async def get_watched_by_user(self, user_name: str) -> Iterable[Watched]:
        return await self._repository.get_watched_by_user(user_name)


    async def add_watched(self, data: WatchedIn) -> Watched | None:
        return await self._repository.add_watched(data)

    async def update_watched(self, watched_id: int, data: WatchedIn) -> Watched | None:
        return await self._repository.update_watched(watched_id, data)

    async def delete_watched(self, watched_id: int) -> bool:
        return await self._repository.delete_watched(watched_id)