"""A module containing user-related routers."""
from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException

from movieapp.container import Container
from movieapp.core.domain.user import User, UserIn
from movieapp.infrastructure.dto.tokendto import TokenDTO
from movieapp.infrastructure.dto.userdto import UserDTO
from movieapp.infrastructure.services.iuser import IUserService

router = APIRouter()


@router.post("/register", response_model=UserDTO, status_code=201)
@inject
async def register_user(
    user: UserIn,
    service: IUserService = Depends(Provide[Container.user_service]),
) -> dict:
    """A router coroutine for registering new user

    Args:
        user (UserIn): The user input data.
        service (IUserService, optional): The injected user service.

    Raises:
        HTTPException: 400 if user already exist.

    Returns:
        dict: The user DTO details.
    """

    if new_user := await service.register_user(user):
        return UserDTO(**dict(new_user)).model_dump()

    raise HTTPException(
        status_code=400,
        detail="The user with provided e-mail or name already exists",
    )



@router.post("/token", response_model=TokenDTO, status_code=200)
@inject
async def authenticate_user(
    user: UserIn,
    service: IUserService = Depends(Provide[Container.user_service]),
) -> dict:
    """A router coroutine for authenticating users.

    Args:
        user (UserIn): The user input data.
        service (IUserService, optional): The injected user service.

    Raises:
        HTTPException: 401 if provided incorrect credentials.

    Returns:
        dict: The token DTO details.
    """

    if token_details := await service.authenticate_user(user):
        print("user confirmed")
        return token_details.model_dump()

    raise HTTPException(
        status_code=401,
        detail="Provided incorrect credentials",
    )

