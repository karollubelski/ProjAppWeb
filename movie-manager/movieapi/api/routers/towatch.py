from typing import Iterable, Any
from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException

from movieapi.container import Container
from movieapi.core.domain.towatch import ToWatch, ToWatchIn
from movieapi.infrastructure.services.itowatch import IToWatchService

router = APIRouter()


@router.post("/create", response_model=ToWatch, status_code=201)
@inject
async def create_towatch(
        towatch: ToWatchIn,
        service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> dict:
    
    new_towatch = await service.add_towatch(towatch)
    return new_towatch.model_dump() if new_towatch else {}


@router.put("/{towatch_id}", response_model=ToWatch, status_code=201)
@inject
async def update_towatch(
    towatch_id: int,
    updated_towatch: ToWatchIn,
    service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> dict:

    if await service.get_towatch_by_id(towatch_id=towatch_id):

        new_updated_towatch = await service.update_towatch(
            towatch_id=towatch_id,
            data=updated_towatch,
        )
        return new_updated_towatch.model_dump() if new_updated_towatch else {}


    raise HTTPException(status_code=404, detail="ToWatch not found")




@router.delete("/{towatch_id}", status_code=204)
@inject
async def delete_towatch(
    towatch_id: int,
    service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> None:
    
    if await service.get_towatch_by_id(towatch_id=towatch_id):
        
        await service.delete_towatch(towatch_id)
        return


    raise HTTPException(status_code=404, detail="ToWatch not found")



@router.get("/{towatch_id}", response_model=ToWatch, status_code=200)
@inject
async def get_towatch_by_id(
        towatch_id: int,
        service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> dict:
    
    if towatch := await service.get_towatch_by_id(towatch_id):
        return towatch.model_dump()

    raise HTTPException(status_code=404, detail="ToWatch not found")




@router.get("/all", response_model=Iterable[ToWatch], status_code=200)
@inject
async def get_all_towatches(
    service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> Iterable:
    
    towatch = await service.get_all_towatches()
    return towatch





@router.get("/movie/{movie_title}", response_model=Iterable[ToWatch], status_code=200)
@inject
async def get_towatch_by_movie(
        movie_title: str,
        service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> Iterable:

    towatches = await service.get_towatch_by_movie(movie_title)

    return towatches


@router.get("/user/{user_name}", response_model=Iterable[ToWatch], status_code=200)
@inject
async def get_towatch_by_user(
    user_name: str,
    service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> Iterable:
    
    towatch = await service.get_towatch_by_user(user_name)

    return towatch

