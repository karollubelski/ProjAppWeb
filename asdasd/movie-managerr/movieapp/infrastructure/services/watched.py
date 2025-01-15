from typing import Iterable



from movieapp.core.domain.watched import Watched, WatchedIn # Zmieniona nazwa i dodane importy

from movieapp.core.repositories.iwatched import IWatchedRepository # Zmieniona nazwa i import
from movieapp.infrastructure.services.iwatched import IWatchedService # Zmieniony import



class WatchedService(IWatchedService):  # Zmienione nazwy

    _repository: IWatchedRepository

    def __init__(self, repository: IWatchedRepository) -> None:


        self._repository = repository




    async def get_watched_by_id(self, watched_id: int) -> Watched | None:

        return await self._repository.get_watched_by_id(watched_id)





    async def get_watched_by_movie_title(self, movie_title: str) -> Iterable[Watched]:

        return await self._repository.get_watched_by_movie_title(movie_title)





    async def get_watched_by_user(self, user_id) -> Iterable[Watched]:

        return await self._repository.get_watched_by_user(user_id)


    async def add_watched(self, data: WatchedIn) -> Watched | None: #watched


        return await self._repository.add_watched(data)  # Zmienione nazwy


    async def update_watched(

        self,
        watched_id: int,
        data: WatchedIn,

    ) -> Watched | None:


        return await self._repository.update_watched(watched_id=watched_id, data=data
        )



    async def delete_watched(self, watched_id: int) -> bool:



        return await self._repository.delete_watched(watched_id)