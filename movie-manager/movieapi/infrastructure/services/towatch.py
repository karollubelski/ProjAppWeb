from typing import Iterable, Any

from movieapi.core.domain.towatch import ToWatch, ToWatchIn
from movieapi.core.repositories.itowatch import IToWatchRepository
from movieapi.infrastructure.services.itowatch import IToWatchService

class ToWatchService(IToWatchService):

    _repository: IToWatchRepository

    def __init__(self, repository: IToWatchRepository) -> None:
        self._repository = repository


    async def get_towatch_by_id(self, towatch_id: int) -> ToWatch | None:
        return await self._repository.get_towatch_by_id(towatch_id)

    async def get_all_towatches(self) -> Iterable[ToWatch]:
        return await self._repository.get_all_towatches()

    async def get_towatch_by_movie(self, movie_title: str) -> Iterable[ToWatch]:
        return await self._repository.get_towatch_by_movie(movie_title)

    async def get_towatch_by_user(self, user_name: str) -> Iterable[ToWatch]:
        return await self._repository.get_towatch_by_user(user_name)


    async def add_towatch(self, data: ToWatchIn) -> ToWatch | None:
        return await self._repository.add_towatch(data)

    async def update_towatch(self, towatch_id: int, data: ToWatchIn) -> ToWatch | None:
        return await self._repository.update_towatch(towatch_id=towatch_id, data=data)


    async def delete_towatch(self, towatch_id: int) -> bool:
        return await self._repository.delete_towatch(towatch_id)