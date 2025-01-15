from typing import Iterable

from movieapp.core.domain.towatch import ToWatch, ToWatchIn  # Zmienione importy
from movieapp.core.repositories.itowatch import IToWatchRepository  # Zmieniony import
from movieapp.infrastructure.services.itowatch import IToWatchService  # Zmienione importy


class ToWatchService(IToWatchService): # Zmienione nazwy

    _repository: IToWatchRepository  # Zmienione nazwy

    def __init__(self, repository: IToWatchRepository) -> None:  # Zmienione nazwy

        self._repository = repository



    async def get_towatch_by_id(self, towatch_id: int) -> ToWatch | None:

        return await self._repository.get_towatch_by_id(towatch_id)




    async def get_all_towatch_movies(self) -> Iterable[ToWatch]:

        return await self._repository.get_all_towatch_movies()




    async def get_towatch_by_movie_title(self, movie_title: str) -> Iterable[ToWatch]:


        return await self._repository.get_towatch_by_movie_title(movie_title)


    async def get_towatch_by_user(self, user_id) -> Iterable[ToWatch]:

        return await self._repository.get_towatch_by_user(user_id)




    async def add_towatch(self, data: ToWatchIn) -> ToWatch | None:


        return await self._repository.add_towatch(data)


    async def update_towatch(
        self,

        towatch_id: int,

        data: ToWatchIn,


    ) -> ToWatch | None: # Zmienione nazwy


        return await self._repository.update_towatch(
            towatch_id=towatch_id,
            data=data,

        )


    async def delete_towatch(self, towatch_id: int) -> bool: # Zmienione nazwy

        return await self._repository.delete_towatch(towatch_id)