<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BrewStatus;
use App\Enums\CaffeineLevel;
use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use App\Enums\TeaType;
use App\Models\Brew;
use App\Models\Steep;
use App\Models\Tea;
use App\Models\Teapot;

class MemoryStore
{
    /** @var array<string, Teapot> */
    private array $teapots = [];

    /** @var array<string, Tea> */
    private array $teas = [];

    /** @var array<string, Brew> */
    private array $brews = [];

    /** @var array<string, Steep> */
    private array $steeps = [];

    // ========================================
    // Teapot methods
    // ========================================

    /**
     * @return Teapot[]
     */
    public function listTeapots(
        int $page = 1,
        int $limit = 20,
        ?TeapotMaterial $material = null,
        ?TeapotStyle $style = null,
    ): array {
        $filtered = array_filter($this->teapots, function (Teapot $t) use ($material, $style) {
            if ($material !== null && $t->material !== $material) {
                return false;
            }
            if ($style !== null && $t->style !== $style) {
                return false;
            }
            return true;
        });

        $offset = ($page - 1) * $limit;
        return array_slice(array_values($filtered), $offset, $limit);
    }

    public function countTeapots(
        ?TeapotMaterial $material = null,
        ?TeapotStyle $style = null,
    ): int {
        $filtered = array_filter($this->teapots, function (Teapot $t) use ($material, $style) {
            if ($material !== null && $t->material !== $material) {
                return false;
            }
            if ($style !== null && $t->style !== $style) {
                return false;
            }
            return true;
        });

        return count($filtered);
    }

    public function createTeapot(Teapot $teapot): void
    {
        $this->teapots[$teapot->id] = $teapot;
    }

    public function getTeapot(string $id): ?Teapot
    {
        return $this->teapots[$id] ?? null;
    }

    public function updateTeapot(Teapot $teapot): void
    {
        $this->teapots[$teapot->id] = $teapot;
    }

    public function deleteTeapot(string $id): bool
    {
        if (!isset($this->teapots[$id])) {
            return false;
        }
        unset($this->teapots[$id]);
        return true;
    }

    // ========================================
    // Tea methods
    // ========================================

    /**
     * @return Tea[]
     */
    public function listTeas(
        int $page = 1,
        int $limit = 20,
        ?TeaType $type = null,
        ?CaffeineLevel $caffeineLevel = null,
    ): array {
        $filtered = array_filter($this->teas, function (Tea $t) use ($type, $caffeineLevel) {
            if ($type !== null && $t->type !== $type) {
                return false;
            }
            if ($caffeineLevel !== null && $t->caffeine_level !== $caffeineLevel) {
                return false;
            }
            return true;
        });

        $offset = ($page - 1) * $limit;
        return array_slice(array_values($filtered), $offset, $limit);
    }

    public function countTeas(
        ?TeaType $type = null,
        ?CaffeineLevel $caffeineLevel = null,
    ): int {
        $filtered = array_filter($this->teas, function (Tea $t) use ($type, $caffeineLevel) {
            if ($type !== null && $t->type !== $type) {
                return false;
            }
            if ($caffeineLevel !== null && $t->caffeine_level !== $caffeineLevel) {
                return false;
            }
            return true;
        });

        return count($filtered);
    }

    public function createTea(Tea $tea): void
    {
        $this->teas[$tea->id] = $tea;
    }

    public function getTea(string $id): ?Tea
    {
        return $this->teas[$id] ?? null;
    }

    public function updateTea(Tea $tea): void
    {
        $this->teas[$tea->id] = $tea;
    }

    public function deleteTea(string $id): bool
    {
        if (!isset($this->teas[$id])) {
            return false;
        }
        unset($this->teas[$id]);
        return true;
    }

    // ========================================
    // Brew methods
    // ========================================

    /**
     * @return Brew[]
     */
    public function listBrews(
        int $page = 1,
        int $limit = 20,
        ?BrewStatus $status = null,
        ?string $teapotId = null,
        ?string $teaId = null,
    ): array {
        $filtered = array_filter($this->brews, function (Brew $b) use ($status, $teapotId, $teaId) {
            if ($status !== null && $b->status !== $status) {
                return false;
            }
            if ($teapotId !== null && $b->teapot_id !== $teapotId) {
                return false;
            }
            if ($teaId !== null && $b->tea_id !== $teaId) {
                return false;
            }
            return true;
        });

        $offset = ($page - 1) * $limit;
        return array_slice(array_values($filtered), $offset, $limit);
    }

    public function countBrews(
        ?BrewStatus $status = null,
        ?string $teapotId = null,
        ?string $teaId = null,
    ): int {
        $filtered = array_filter($this->brews, function (Brew $b) use ($status, $teapotId, $teaId) {
            if ($status !== null && $b->status !== $status) {
                return false;
            }
            if ($teapotId !== null && $b->teapot_id !== $teapotId) {
                return false;
            }
            if ($teaId !== null && $b->tea_id !== $teaId) {
                return false;
            }
            return true;
        });

        return count($filtered);
    }

    /**
     * @return Brew[]
     */
    public function listBrewsByTeapot(string $teapotId, int $page = 1, int $limit = 20): array
    {
        return $this->listBrews($page, $limit, teapotId: $teapotId);
    }

    public function countBrewsByTeapot(string $teapotId): int
    {
        return $this->countBrews(teapotId: $teapotId);
    }

    public function createBrew(Brew $brew): void
    {
        $this->brews[$brew->id] = $brew;
    }

    public function getBrew(string $id): ?Brew
    {
        return $this->brews[$id] ?? null;
    }

    public function updateBrew(Brew $brew): void
    {
        $this->brews[$brew->id] = $brew;
    }

    public function deleteBrew(string $id): bool
    {
        if (!isset($this->brews[$id])) {
            return false;
        }
        unset($this->brews[$id]);
        // Also delete associated steeps
        foreach ($this->steeps as $steepId => $steep) {
            if ($steep->brew_id === $id) {
                unset($this->steeps[$steepId]);
            }
        }
        return true;
    }

    // ========================================
    // Steep methods
    // ========================================

    /**
     * @return Steep[]
     */
    public function listSteepsByBrew(string $brewId, int $page = 1, int $limit = 20): array
    {
        $filtered = array_filter($this->steeps, fn (Steep $s) => $s->brew_id === $brewId);
        // Sort by steep number
        usort($filtered, fn (Steep $a, Steep $b) => $a->steep_number <=> $b->steep_number);

        $offset = ($page - 1) * $limit;
        return array_slice($filtered, $offset, $limit);
    }

    public function countSteepsByBrew(string $brewId): int
    {
        return count(array_filter($this->steeps, fn (Steep $s) => $s->brew_id === $brewId));
    }

    public function createSteep(Steep $steep): void
    {
        $this->steeps[$steep->id] = $steep;
    }

    public function getSteep(string $id): ?Steep
    {
        return $this->steeps[$id] ?? null;
    }

    public function getNextSteepNumber(string $brewId): int
    {
        $count = $this->countSteepsByBrew($brewId);
        return $count + 1;
    }
}
