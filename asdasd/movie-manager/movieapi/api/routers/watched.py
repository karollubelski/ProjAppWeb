from typing import Iterable, Any
from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException

from movieapi.container import Container
from movieapi.core.domain.watched import Watched, WatchedIn
from movieapi.infrastructure.services.iwatched import IWatchedService

router = APIRouter()


@router.post("/create", response_model=Watched, status_code=201)
@inject
async def create_watched(
        watched: WatchedIn,
        service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> dict:
    
    new_watched = await service.add_watched(watched)
    return new_watched.model_dump() if new_watched else {}


@router.put("/{watched_id}", response_model=Watched, status_code=201)
@inject
async def update_watched(
    watched_id: int,
    updated_watched: WatchedIn,
    service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> dict:
    
    if await service.get_watched_by_id(watched_id=watched_id):
        new_updated_watched = await service.update_watched(
            watched_id=watched_id,
            data=updated_watched,
        )
        return new_updated_watched.model_dump() if new_updated_watched else {}

    raise HTTPException(status_code=404, detail="Watched not found")


@router.delete("/{watched_id}", status_code=204)
@inject
async def delete_watched(
    watched_id: int,
    service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> None:
    
    if await service.get_watched_by_id(watched_id=watched_id):
        await service.delete_watched(watched_id)
        return

    raise HTTPException(status_code=404, detail="Watched not found")


@router.get("/{watched_id}", response_model=Watched, status_code=200)
@inject
async def get_watched_by_id(
        watched_id: int,
        service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> dict:
    
    if watched := await service.get_watched_by_id(watched_id):
        return watched.model_dump()

    raise HTTPException(status_code=404, detail="Watched not found")


@router.get("/all", response_model=Iterable[Watched], status_code=200)
@inject
async def get_all_watcheds(
    service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> Iterable:
    
    watched = await service.get_all_watcheds()
    return watched


@router.get("/movie/{movie_title}", response_model=Iterable[Watched], status_code=200)
@inject
async def get_watched_by_movie(
        movie_title: str,
        service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> Iterable:
    
    watcheds = await service.get_watched_by_movie(movie_title)
    return watcheds



@router.get("/user/{user_name}", response_model=Iterable[Watched], status_code=200)
@inject
async def get_watched_by_user(
    user_name: str,
    service: IWatchedService = Depends(Provide[Container.watched_service]),
) -> Iterable:
    
    watched = await service.get_watched_by_user(user_name)
    return watched

