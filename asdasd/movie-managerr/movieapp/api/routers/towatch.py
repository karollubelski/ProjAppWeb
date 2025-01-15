from typing import Iterable, Any

from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException, Security
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from jose import jwt
from movieapp.infrastructure.utils import consts

from movieapp.container import Container
from movieapp.core.domain.towatch import ToWatch, ToWatchIn, ToWatchBroker # Zmieniona nazwa i dodany import
from movieapp.infrastructure.services.itowatch import IToWatchService  # Zmieniona nazwa


router = APIRouter()
bearer_scheme = HTTPBearer()


@router.post("/create", response_model=ToWatch, status_code=201) # Zmieniona nazwa modelu i metody
@inject
async def create_towatch(
    towatch: ToWatchIn, # Zmieniona nazwa parametru
    service: IToWatchService = Depends(Provide[Container.towatch_service]), # Zmieniona nazwa
    credentials: HTTPAuthorizationCredentials = Security(bearer_scheme),


) -> dict:



    token = credentials.credentials

    token_payload = jwt.decode(

        token,

        key=consts.SECRET_KEY,

        algorithms=[consts.ALGORITHM],

    )
    user_uuid = token_payload.get("sub")




    if not user_uuid:
        raise HTTPException(status_code=403, detail="Unauthorized")



    extended_towatch_data = ToWatchBroker( # Zmieniona nazwa
        user_id = user_uuid, #dodane user_id
        **towatch.model_dump(), #nazwa zmiennej

    )




    new_towatch = await service.add_towatch(extended_towatch_data) #nazwa zmiennej

    return new_towatch.model_dump() if new_towatch else {}



@router.get("/{towatch_id}", response_model=ToWatch, status_code=200) # Zmienione nazwy i dodana adnotacja typu
@inject
async def get_towatch_by_id(

    towatch_id: int, #nazwa
    service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> dict:



    if towatch := await service.get_towatch_by_id(towatch_id):

        return towatch.model_dump()




    raise HTTPException(status_code=404, detail="ToWatch not found")



@router.get("/movie_title/{movie_title}", response_model=Iterable[ToWatch], status_code=200)  # Zmieniona nazwa i typ
@inject
async def get_towatch_by_movie( # Zmieniona nazwa
    movie_title: str, #movie_title
    service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> Iterable:  # Dodana adnotacja Iterable



    towatch_movies = await service.get_towatch_by_movie_title(movie_title) #movie title


    return towatch_movies




@router.get("/user/{user_id}", response_model=Iterable[ToWatch], status_code=200)  # Zmieniona nazwa i typ  #user_id

@inject
async def get_towatch_by_user( #user_id # Zmieniona nazwa

    user_id,

    service: IToWatchService = Depends(Provide[Container.towatch_service]),
) -> Iterable:  # Dodana adnotacja Iterable



    towatch_movies = await service.get_towatch_by_user(user_id) # Zmieniona nazwa


    return towatch_movies




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



        return new_updated_towatch.model_dump() if new_updated_towatch \
            else {}




    raise HTTPException(status_code=404, detail="ToWatch not found")





@router.delete("/{towatch_id}", status_code=204) # Zmieniona nazwa
@inject
async def delete_towatch(

    towatch_id: int,
    service: IToWatchService = Depends(Provide[Container.towatch_service]), # Zmieniona nazwa i typ


) -> None:



    if await service.get_towatch_by_id(towatch_id=towatch_id):
        await service.delete_towatch(towatch_id)


        return


    raise HTTPException(status_code=404, detail="ToWatch not found")