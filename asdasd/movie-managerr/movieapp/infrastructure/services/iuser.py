"""A module containing user service."""
from abc import ABC, abstractmethod

from pydantic import UUID5

from movieapp.core.domain.user import User, UserIn
from movieapp.infrastructure.dto.userdto import UserDTO
from movieapp.infrastructure.dto.tokendto import TokenDTO


class IUserService(ABC):
    """An abstract class for user service."""
    @abstractmethod
    async def register_user(self, user: UserIn) -> UserDTO | None:
        pass
    """A method registering new user.

    Args:
        user (UserIn): The user input data.

    Returns:
        UserDTO | None: The user DTO model.
    """

    @abstractmethod
    async def authenticate_user(self, user: UserIn) -> TokenDTO | None:
        pass
    """The method authenticating the user.

    Args:
        user (UserIn): The user data.

    Returns:
        TokenDTO | None: The token details.
    """


    @abstractmethod
    async def get_by_uuid(self, uuid: UUID5) -> UserDTO | None:
        pass
    """A method getting user by UUID.

    Args:
        uuid (UUID5): The UUID of the user.

    Returns:
        UserDTO | None: The user data, if found.
    """

    @abstractmethod
    async def get_by_email(self, email: str) -> UserDTO | None:
        pass
    """A method getting user by email.

    Args:
        email (str): The email of the user.

    Returns:
        UserDTO | None: The user data, if found.
    """

    @abstractmethod
    async def get_by_name(self, name: str) -> UserDTO | None:
        pass
    """A method getting user by name.

    Args:
        name (str): The name of the user.

    Returns:
        UserDTO | None: The user data, if found.
    """