from abc import ABC, abstractmethod
from typing import Iterable

from movieapp.core.domain.towatch import ToWatch, ToWatchIn # Zmienione importy


class IToWatchService(ABC):  # Zmieniona nazwa

    @abstractmethod
    async def get_towatch_by_id(self, towatch_id: int) -> ToWatch | None:  # Zmienione nazwy
        pass

    @abstractmethod
    async def get_all_towatch_movies(self) -> Iterable[ToWatch]:  # Zmienione nazwy
        pass

    @abstractmethod
    async def get_towatch_by_movie_title(self, movie_title: str) -> Iterable[ToWatch]:  # Zmienione nazwy
        pass


    @abstractmethod
    async def get_towatch_by_user(self, user_id) -> Iterable[ToWatch]: #user_id # Zmieniona nazwa
        pass

    @abstractmethod
    async def add_towatch(self, data: ToWatchIn) -> ToWatch | None:  # Zmienione nazwy
        pass


    @abstractmethod
    async def update_towatch( # Zmieniona nazwa
        self,
        towatch_id: int,  # Zmienione nazwy
        data: ToWatchIn,
    ) -> ToWatch | None:
        pass


    @abstractmethod
    async def delete_towatch(self, towatch_id: int) -> bool:  # Zmieniona nazwa
        pass