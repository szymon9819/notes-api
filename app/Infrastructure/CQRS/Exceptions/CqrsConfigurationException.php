<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Exceptions;

use LogicException;

abstract class CqrsConfigurationException extends LogicException {}
