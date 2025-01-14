from abc import ABC, abstractmethod
from typing import Iterable

from movieapi.core.domain.watched import Watched, WatchedIn


class IWatchedService(ABC):
    @abstractmethod
    async def get_watched_by_id(self, watched_id: int) -> Watched | None:
        pass

    @abstractmethod
    async def get_all_watcheds(self) -> Iterable[Watched]:
        pass

    @abstractmethod
    async def get_watched_by_movie(self, movie_title: str) -> Iterable[Watched]:
        pass

    @abstractmethod
    async def get_watched_by_user(self, user_name: str) -> Iterable[Watched]:
        pass

    @abstractmethod
    async def add_watched(self, data: WatchedIn) -> Watched | None:
        pass

    @abstractmethod
    async def update_watched(
        self,
        watched_id: int,
        data: WatchedIn,
    ) -> Watched | None:
        pass

    @abstractmethod
    async def delete_watched(self, watched_id: int) -> bool:
        pass