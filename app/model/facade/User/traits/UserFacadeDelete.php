<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\User;
use LogicException;

trait UserFacadeDelete
{

	/**
	 * Delete user by id
	 * @param int $id User ID
	 * @return bool
	 */
	public function deleteById($id)
	{
		$user = $this->userRepo->find($id);
		return $this->delete($user);
	}

	/**
	 * Delete user or throw exception
	 * @param User $user
	 * @return User
	 * @throws CantDeleteUserException
	 */
	public function delete(User $user)
	{
		if ($this->isDeletable($user)) {
			$this->clearPermissions($user);
			$this->clearCommunication($user);
			$this->clearCandidate($user);
			$this->clearActions($user);
			$this->em->remove($user);
			$this->em->flush();
			return $user;
		}
		throw new CantDeleteUserException('You\'re only one admin');
	}

	private function clearPermissions(User $user)
	{
		foreach ($this->companyFacade->findPermissions(NULL, $user) as $permission) {
			$this->companyPermissionRepo->delete($permission);
		}
		return TRUE;
	}

	private function clearCommunication(User $user)
	{
		foreach ($this->communicationFacade->findSenders($user) as $sender) {
			$this->communicationFacade->delete($sender);
		}
		return TRUE;
	}

	private function clearCandidate(User $user)
	{
		if ($user->person && $user->person->candidate->id) {
			$this->candidateFacade->delete($user->person->candidate);
		}
		return TRUE;
	}

	private function clearActions(User $user)
	{
		foreach ($this->actionRepo->findByUser($user) as $action) {
			$this->actionRepo->delete($action);
		}
		return TRUE;
	}

	public function isDeletable(User $user)
	{
		return !$this->isLastAdminForSomeCompany($user);
	}

	/**
	 * If some user is last admin for some company then return TRUE
	 * @param User $user
	 * @return boolean
	 */
	private function isLastAdminForSomeCompany(User $user)
	{
		$permissions = $this->companyFacade->findPermissions(NULL, $user);
		foreach ($permissions as $permission) {
			if (
					count($permission->company->adminAccesses) === 1 &&
					$user->id === $permission->company->adminAccesses->first()->user->id
			) {
				return TRUE;
			}
		}
		return FALSE;
	}

}

class CantDeleteUserException extends LogicException
{

}
