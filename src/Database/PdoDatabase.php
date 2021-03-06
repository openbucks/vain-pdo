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

namespace Vain\Pdo\Database;

use Vain\Core\Database\AbstractDatabase;
use Vain\Core\Exception\LevelIntegrityDatabaseException;
use Vain\Core\Database\Mvcc\MvccDatabaseInterface;
use Vain\Pdo\Exception\CommunicationPdoDatabaseException;
use Vain\Pdo\Exception\QueryPdoDatabaseException;
use Vain\Core\Database\Generator\DatabaseGeneratorInterface;
use Vain\Pdo\Cursor\PdoCursor;

/**
 * Class PDOAdapter
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 *
 * @method \PDO getConnection
 */
class PdoDatabase extends AbstractDatabase implements MvccDatabaseInterface
{
    private $level = 0;

    /**
     * @return int
     */
    public function getLevel() : int
    {
        return $this->level;
    }

    /**
     * @inheritDoc
     */
    public function startTransaction() : bool
    {
        if (0 < $this->level) {
            $this->level++;

            return true;
        }

        if (0 > $this->level) {
            throw new LevelIntegrityDatabaseException($this, $this->level);
        }

        try {
            $this->level++;

            return $this->getConnection()->beginTransaction();
        } catch (\PDOException $e) {
            throw new CommunicationPDODatabaseException($this, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function commitTransaction() : bool
    {
        $this->level--;

        if (0 < $this->level) {
            return true;
        }

        if (0 > $this->level) {
            throw new LevelIntegrityDatabaseException($this, $this->level);
        }

        try {
            return $this->getConnection()->commit();
        } catch (\PDOException $e) {
            throw new CommunicationPDODatabaseException($this, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function rollbackTransaction() : bool
    {
        $this->level--;

        if (0 < $this->level) {
            return true;
        }

        if (0 > $this->level) {
            throw new LevelIntegrityDatabaseException($this, $this->level);
        }

        try {
            return $this->getConnection()->rollBack();
        } catch (\PDOException $e) {
            throw new CommunicationPDODatabaseException($this, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function runQuery($query, array $bindParams, array $bindParamTypes = []) : DatabaseGeneratorInterface
    {
        $statement = $this->getConnection()->prepare($query);
        if (false == $statement->execute($bindParams)) {
            throw new QueryPDODatabaseException($this, $statement->errorCode(), $statement->errorInfo());
        }

        return $this->getGenerator(new PDOCursor($statement));
    }
}
