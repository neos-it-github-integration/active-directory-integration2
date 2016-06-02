<?php

/**
 * Base class for synchronization between WordPress and Active Directory.
 *
 * @author  Danny Meißner <dme@neos-it.de>
 *
 * @access
 */
abstract class Adi_Synchronization_Abstract
{
	/* @var Multisite_Configuration_Service */
	protected $configuration;

	/* @var Ldap_Connection */
	protected $connection;

	/* @var Ldap_Attribute_Service */
	protected $attributeService;

	/* @var Logger $logger */
	private $logger;

	/* @var Ldap_ConnectionDetails */
	protected $connectionDetails;

	private $time = 0;
	
	/**
	 * Execution time in seconds which is required for the long-running tasks
	 */
	const REQUIRED_EXECUTION_TIME_IN_SECONDS = 18000;


	/**
	 * @param Multisite_Configuration_Service $configuration
	 * @param Ldap_Connection $connection
	 * @param Ldap_Attribute_Service  $attributeService
	 * */
	public function __construct(Multisite_Configuration_Service $configuration,
								Ldap_Connection $connection,
								Ldap_Attribute_Service $attributeService)
	{
		$this->configuration = $configuration;
		$this->connection = $connection;
		$this->attributeService = $attributeService;
		$this->connectionDetails = new Ldap_ConnectionDetails();

		$this->logger = Logger::getLogger(__CLASS__);
	}

	/**
	 * Increase the execution time of a php script to at least 1 hour.
	 */
	public function increaseExecutionTime()
	{
		if (ini_get('max_execution_time') >= self::REQUIRED_EXECUTION_TIME_IN_SECONDS) {
			return;
		}

		ini_set('max_execution_time', self::REQUIRED_EXECUTION_TIME_IN_SECONDS);

		if (ini_get('max_execution_time') >= self::REQUIRED_EXECUTION_TIME_IN_SECONDS) {
			return;
		}

		$this->logger->warn('Can not increase PHP configuration option \'max_execution_time\' to ' . self::REQUIRED_EXECUTION_TIME_IN_SECONDS . ' seconds.');
	}

	/**
	 * Establish a connection to the Active Directory server.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return bool connection success
	 */
	public function connectToAdLdap($username, $password)
	{
		$this->connectionDetails = new Ldap_ConnectionDetails();
		$this->connectionDetails->setUsername($username);
		$this->connectionDetails->setPassword($password);

		$this->connection->connect($this->connectionDetails);
		return $this->connection->checkConnection($username, $password);
	}

	/**
	 * Start timer.
	 */
	public function startTimer()
	{
		$this->time = time();
	}

	/**
	 * Get the passed time since startTimer was called.
	 *
	 * @return int
	 */
	public function getElapsedTime()
	{
		return time() - $this->time;
	}

	/**
	 * Return an array with the the mapping between the Active Directory sAMAccountName (key) and their WordPress username (value).
	 *
	 * @return array|hashmap key is Active Directory objectGUID, value is WordPress username
	 */
	public function findActiveDirectoryUsernames()
	{
		$users = $this->findActiveDirectoryUsers();
		$r = array();
		
		foreach ($users as $user) {
			$guid = get_user_meta($user->ID, ADI_PREFIX . Adi_User_Persistence_Repository::META_KEY_OBJECT_GUID, true);
			
			$wpUsername = $user->user_login;
			$r[strtolower($guid)] = $wpUsername;
		}

		return $r;
	}

	/**
	 * Find all WordPress users which have their origin in the Active Directory.
	 *
	 * It searches the WordPress user table for the meta key 'samaccountname'. The attribute 'samaccountname' is synchronized during login/update.
	 *
	 * @param null|int $userId if specified it only finds the user with the given ID
	 *
	 * @return array
	 */
	public function findActiveDirectoryUsers($userId = null)
	{
		$args = array(
			'blog_id'    => get_current_blog_id(),
			'meta_key'   => ADI_PREFIX . Adi_User_Persistence_Repository::META_KEY_ACTIVE_DIRECTORY_SAMACCOUNTNAME,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => ADI_PREFIX . Adi_User_Persistence_Repository::META_KEY_ACTIVE_DIRECTORY_SAMACCOUNTNAME,
					'value'   => '',
					'compare' => '!=',
				),
			),
			'exclude'    => array(1)
		);

		if ($userId) {
			$args['include'] = array($userId);
		}

		$users = get_users($args);
		return $users;
	}

	/**
	 * Check if attribute to be synced to Active Directory is empty
	 *
	 * Check if the attribute value for an attribute is empty, if yes return an array.
	 * Workaround to prevent adLDAP from syncing "Array" as a value for an attribute to the Active Directory.
	 *
	 * @param array $attributesToSync
	 * @param string $metaKey
	 *
	 * @return bool
	 */
	public function isAttributeValueEmpty($attributesToSync, $metaKey)
	{
		if (empty($attributesToSync[$metaKey][0])) {
			return true;
		}

		return false;
	}
}