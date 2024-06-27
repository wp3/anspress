<?php
/**
 * Abstract policy class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Interfaces\ModelInterface;
use AnsPress\Interfaces\PolicyInterface;
use InvalidArgumentException;
use WP_User;

use function Patchwork\getCalledClass;

use const Patchwork\CodeManipulation\Actions\RedefinitionOfNew\CALLED_CLASS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract policy class.
 *
 * @package AnsPress\Classes
 */
abstract class AbstractPolicy implements PolicyInterface {
	/**
	 * Name of the policy.
	 *
	 * @var string
	 */
	public const POLICY_NAME = '';

	/**
	 * List of abilities that the policy can handle.
	 *
	 * @var array
	 */
	protected array $abilities = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 * @throws InvalidArgumentException If the POLICY_NAME constant is not defined.
	 */
	public function __construct() {
		if ( empty( $this->getPolicyName() ) ) {
			throw new InvalidArgumentException( 'POLICY_NAME constant must be defined in the child class.' );
		}
	}

	/**
	 * Get the name of the policy.
	 *
	 * @return string
	 */
	public function getPolicyName(): string {
		return $this::POLICY_NAME;
	}

	/**
	 * Validation for the context of the ability.
	 *
	 * @param string $ability Ability name.
	 * @param array  $context Context arr.
	 * @return bool
	 */
	public function validateContext( string $ability, array $context = array() ): bool {
		if ( ! isset( $this->abilities[ $ability ] ) ) {
			return false;
		}

		// If ability context is empty, skip validation.
		if ( empty( $this->abilities[ $ability ] ) ) {
			return true;
		}

		// Check if does not have any required keys then return false.
		if ( ! empty( array_diff( $this->abilities[ $ability ], array_keys( $context ) ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Perform pre-authorization checks before any specific policy method.
	 *
	 * This method can be used to implement global checks that apply to all actions.
	 * Returning a non-null value will bypass the specific policy checks.
	 *
	 * @param string       $ability The ability being checked (e.g., 'view', 'create').
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The context of the ability.
	 * @return bool|null Null to proceed to specific policy method, or a boolean to override.
	 */
	public function before( string $ability, ?WP_User $user, array $context = array() ): ?bool {
		return null;
	}

	/**
	 * Determine if the given user can create a new model.
	 *
	 * @param string       $ability The ability being checked (e.g., 'view', 'create').
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The context of the ability.
	 * @return bool True if the user is authorized to create the model, false otherwise.
	 * @throws InvalidArgumentException If the ability is invalid.
	 */
	public function check( string $ability, ?WP_User $user, array $context = array() ): bool {
		// Check if the ability is valid.
		if ( ! in_array( $ability, array_keys( $this->abilities ), true ) ) {
			throw new InvalidArgumentException( 'Invalid ability provided.' );
		}

		// If ability has a method then call it.
		if ( method_exists( $this, $ability ) ) {
			return $this->$ability( $user, $context );
		}

		// Check if the user has the ability to perform the action.
		if ( ! $user->has_cap( $this->getPolicyName() . ':' . $ability ) ) {
			return false;
		}

		return true;
	}
}
