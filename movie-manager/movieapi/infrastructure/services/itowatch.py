from abc import ABC, abstractmethod
from typing import Iterable, Any

from movieapi.core.domain.towatch import ToWatch, ToWatchIn


class IToWatchService(ABC):
    @abstractmethod
    async def get_towatch_by_id(self, towatch_id: int) -> ToWatch | None:
        pass

    @abstractmethod
    async def get_all_towatches(self) -> Iterable[ToWatch]:
        pass


    @abstractmethod
    async def get_towatch_by_movie(self, movie_title: str) -> Iterable[ToWatch]:
        pass


    @abstractmethod
    async def get_towatch_by_user(self, user_name: str) -> Iterable[ToWatch]:
        pass

    @abstractmethod
    async def add_towatch(self, data: ToWatchIn) -> ToWatch | None:
        pass

    @abstractmethod
    async def update_towatch(
            self,
            towatch_id: int,
            data: ToWatchIn
    ) -> ToWatch | None:
        pass

    @abstractmethod
    async def delete_towatch(self, towatch_id: int) -> bool:
        pass