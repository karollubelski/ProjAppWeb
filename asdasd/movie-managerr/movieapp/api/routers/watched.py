from typing import Iterable, Any
from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException

from movieapp.container import Container
from movieapp.core.domain.watched import Watched, WatchedIn # Zmienione nazwy i dodany import
from movieapp.infrastructure.services.iwatched import IWatchedService # Zmieniona nazwa


router = APIRouter()


@router.post("/create", response_model=Watched, status_code=201) # Zmieniona nazwa
@inject
async def create_watched(
    watched: WatchedIn,
    service: IWatchedService = Depends(Provide[Container.watched_service]),  # Zmieniony typ
) -> dict:


    new_watched = await service.add_watched(watched) #zmienione watched


    return new_watched.model_dump() if new_watched else {}


@router.get("/{watched_id}", response_model=Watched, status_code=200)  # Zmienione nazwy i typ
@inject

async def get_watched_by_id(

    watched_id: int,

    service: IWatchedService = Depends(Provide[Container.watched_service]), # Zmieniony typ
) -> dict:





    if watched := await service.get_watched_by_id(watched_id):

        return watched.model_dump()




    raise HTTPException(status_code=404, detail="Watched not found")



@router.get("/movie/{movie_title}", response_model=Iterable[Watched], status_code=200)  # Zmienione nazwy i dodana adnotacja typu

@inject
async def get_watched_by_movie(  # Zmienione nazwy


    movie_title: str,  #movie title
    service: IWatchedService = Depends(Provide[Container.watched_service]), #zmieniony typ


) -> Iterable:  # Dodana adnotacja Iterable
    

    watched_movies = await service.get_watched_by_movie_title(movie_title) #movie title


    return watched_movies



@router.get("/user/{user_id}", response_model=Iterable[Watched], status_code=200)  #user_id # Zmieniona nazwa i typ
@inject
async def get_watched_by_user( #user_id # Zmienione nazwy


    user_id, #user_id
    service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> Iterable:  # Dodana adnotacja Iterable


    watched_movies = await service.get_watched_by_user(user_id)


    return watched_movies





@router.put("/{watched_id}", response_model=Watched, status_code=201) # Zmieniona nazwa
@inject
async def update_watched(

    watched_id: int, # Zmieniona nazwa
    updated_watched: WatchedIn, #zmienione watched
    service: IWatchedService = Depends(Provide[Container.watched_service]),

) -> dict:


    if await service.get_watched_by_id(watched_id=watched_id):

        new_updated_watched = await service.update_watched(

            watched_id=watched_id, #zmieniona nazwa
            data=updated_watched,

        )

        return new_updated_watched.model_dump() if new_updated_watched \
            else {}


    raise HTTPException(status_code=404, detail="Watched not found")




@router.delete("/{watched_id}", status_code=204) # Zmienione nazwy

@inject
async def delete_watched(

    watched_id: int,

    service: IWatchedService = Depends(Provide[Container.watched_service]),


) -> None:

    if await service.get_watched_by_id(watched_id=watched_id):
        await service.delete_watched(watched_id)

        return


    raise HTTPException(status_code=404, detail="Watched not found")