from abc import ABC, abstractmethod
from typing import Any, Iterable

from movieapi.core.domain.watched import WatchedIn


class IWatchedRepository(ABC):
    @abstractmethod
    async def get_watched_by_id(self, watched_id: int) -> Any | None:
        pass

    @abstractmethod
    async def get_all_watcheds(self) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_watched_by_movie(self, movie_title: str) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_watched_by_user(self, user_name: str) -> Iterable[Any]:
        pass

    @abstractmethod
    async def add_watched(self, data: WatchedIn) -> Any | None:
        pass

    @abstractmethod
    async def update_watched(
            self,
            watched_id: int,
            data: WatchedIn,
    ) -> Any | None:
        pass

    @abstractmethod
    async def delete_watched(self, watched_id: int) -> bool:
        pass