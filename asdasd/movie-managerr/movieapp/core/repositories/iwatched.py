from abc import ABC, abstractmethod
from typing import Any, Iterable

from movieapp.core.domain.watched import WatchedIn  # Poprawiony import


class IWatchedRepository(ABC): # Zmieniona nazwa

    @abstractmethod
    async def get_watched_by_id(self, watched_id: int) -> Any | None:
        pass

    @abstractmethod
    async def get_watched_by_movie_title(self, movie_title: str) -> Iterable[Any]:  # Zmieniona nazwa
        pass


    @abstractmethod
    async def get_watched_by_user(self, user_id) -> Iterable[Any]:  # Zmieniona nazwa  #user_id
        pass

    @abstractmethod
    async def add_watched(self, data: WatchedIn) -> Any | None:  # Zmieniona nazwa
        pass

    @abstractmethod
    async def update_watched(  # Zmieniona nazwa
            self,
            watched_id: int,
            data: WatchedIn,
    ) -> Any | None:
        pass

    @abstractmethod
    async def delete_watched(self, watched_id: int) -> bool:  # Zmieniona nazwa
        pass