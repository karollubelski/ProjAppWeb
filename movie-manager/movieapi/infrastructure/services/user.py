"""A module containing user service."""
from pydantic import UUID4

from movieapi.core.domain.user import User, UserIn
from movieapi.core.repositories.iuser import IUserRepository
from movieapi.infrastructure.dto.userdto import UserDTO
from movieapi.infrastructure.dto.tokendto import TokenDTO
from movieapi.infrastructure.services.iuser import IUserService
from movieapi.infrastructure.utils.password import verify_password
from movieapi.infrastructure.utils.token import generate_user_token


class UserService(IUserService):
    """An abstract class for user service."""
    _repository: IUserRepository

    def __init__(self, repository: IUserRepository) -> None:
        self._repository = repository

    async def register_user(self, user: UserIn) -> UserDTO | None:
        """A method registering new user.

        Args:
            user (UserIn): The user input data.

        Returns:
            UserDTO | None: The user DTO model.
        """
        return await self._repository.register_user(user)

    async def authenticate_user(self, user: UserIn) -> TokenDTO | None:
        """The method authenticating the user.

        Args:
            user (UserIn): The user data.

        Returns:
            TokenDTO | None: The token details.
        """

        if user_data := await self._repository.get_by_email(user.email):
            if verify_password(user.password, user_data.password):
                token_details = generate_user_token(user_data.id)
                # trunk-ignore(bandit/B106)
                return TokenDTO(token_type="Bearer", **token_details)

            return None

        return None

    async def get_by_uuid(self, uuid: UUID4) -> UserDTO | None:
        """A method getting user by UUID.

        Args:
            uuid (UUID4): The UUID of the user.

        Returns:
            UserDTO | None: The user data, if found.
        """
        return await self._repository.get_by_uuid(uuid)

    async def get_by_email(self, email: str) -> UserDTO | None:
        """A method getting user by email.

        Args:
            email (str): The email of the user.

        Returns:
            UserDTO | None: The user data, if found.
        """
        return await self._repository.get_by_email(email)

    async def get_by_name(self, name: str) -> UserDTO | None:
        """A method getting user by name.

        Args:
            name (str): The name of the user.

        Returns:
            UserDTO | None: The user data, if found.
        """
        return await self._repository.get_by_name(name) 