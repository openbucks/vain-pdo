<?php
/**
 * Vain Framework
 *
 * PHP Version 7
 *
 * @package   vain-database
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/allflame/vain-database
 */
declare(strict_types = 1);

namespace Vain\Pdo\Exception;

use Vain\Pdo\PdoAdapter;

/**
 * Class CommunicationPDODatabaseException
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CommunicationPdoDatabaseException extends PdoDatabaseException
{
    /**
     * CommunicationPdoDatabaseException constructor.
     *
     * @param PdoAdapter    $database
     * @param \PDOException $e
     */
    public function __construct(PdoAdapter $database, \PDOException $e)
    {
        parent::__construct($database, (string)$e->getCode(), $e->getMessage(), $e);
    }
}