from abc import ABC, abstractmethod
from typing import Iterable

from movieapp.core.domain.watched import Watched, WatchedIn # Zmienione nazwy i import


class IWatchedService(ABC): # Zmieniona nazwa

    @abstractmethod
    async def get_watched_by_id(self, watched_id: int) -> Watched | None:  # Zmienione nazwy i typ
        pass



    @abstractmethod
    async def get_watched_by_movie_title(self, movie_title: str) -> Iterable[Watched]: # Zmieniona nazwa i typ
        pass




    @abstractmethod
    async def get_watched_by_user(self, user_id) -> Iterable[Watched]:  # Zmienione nazwy i typ  #user_id
        pass



    @abstractmethod
    async def add_watched(self, data: WatchedIn) -> Watched | None:  # Zmieniona nazwa i typ
        pass



    @abstractmethod
    async def update_watched( # Zmieniona nazwa
        self,

        watched_id: int,  # Zmienione nazwy
        data: WatchedIn,

    ) -> Watched | None:
        pass



    @abstractmethod
    async def delete_watched(self, watched_id: int) -> bool: # Zmieniona nazwa
        pass