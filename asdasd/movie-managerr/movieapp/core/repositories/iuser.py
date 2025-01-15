"""Module containing user repository abstractions."""
from abc import ABC, abstractmethod
from typing import Any, Iterable
from pydantic import UUID5

from movieapp.core.domain.user import UserIn


class IUserRepository(ABC):
    """An abstract class representing protocol of user repository."""
    @abstractmethod
    async def get_by_uuid(self, uuid: UUID5) -> Any | None:
        pass

    """A method getting user by UUID.

    Args:
        uuid (UUID5): UUID of the user.

    Returns:
        Any | None: The user object if exists.
    """

    @abstractmethod
    async def get_by_email(self, email: str) -> Any | None:
        pass

    """A method getting user by email.

    Args:
        email (str): The email of the user.

    Returns:
        Any | None: The user object if exists.
    """

    @abstractmethod
    async def get_by_name(self, name: str) -> Any | None:
        pass

    """A method getting user by name.

    Args:
        name (str): The name of the user.

    Returns:
        Any | None: The user object if exists.
    """

    @abstractmethod
    async def register_user(self, user: UserIn) -> Any | None:
        pass

    """A method registering new user.

    Args:
        user (UserIn): The user input data.

    Returns:
        Any | None: The new user object.
    """

