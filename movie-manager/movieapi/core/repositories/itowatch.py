from abc import ABC, abstractmethod
from typing import Any, Iterable

from movieapi.core.domain.towatch import ToWatchIn


class IToWatchRepository(ABC):
    @abstractmethod
    async def get_towatch_by_id(self, towatch_id: int) -> Any | None:
        pass

    @abstractmethod
    async def get_all_towatches(self) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_towatch_by_movie(self, movie_title: str) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_towatch_by_user(self, user_name: str) -> Iterable[Any]:
        pass

    @abstractmethod
    async def add_towatch(self, data: ToWatchIn) -> Any | None:
        pass

    @abstractmethod
    async def update_towatch(
            self,
            towatch_id: int,
            data: ToWatchIn,
    ) -> Any | None:
        pass

    @abstractmethod
    async def delete_towatch(self, towatch_id: int) -> bool:
        pass