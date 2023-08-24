<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Api\Data;

interface AccountInterface
{
    /**
     * Account id
     *
     * @return int
     */
    public function getEntityId(): int;

    /**
     * Retrieve account id
     *
     * @return int
     */
    public function getAccountId(): int;

    /**
     * Set account id
     *
     * @param int $id
     * @return $this
     */
    public function setAccountId($id): static;

    /**
     * Retrieve account id
     *
     * @return string
     */
    public function getAccountName(): string;

    /**
     * Set account id
     *
     * @param string $name
     * @return $this
     */
    public function setAccountName($name): static;

    /**
     * Retrieve is active
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Retrieve is active
     *
     * @param int $active
     * @return $this
     */
    public function setIsActive($active): static;
}
