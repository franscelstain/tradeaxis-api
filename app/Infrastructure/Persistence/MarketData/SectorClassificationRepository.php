<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class SectorClassificationRepository
{
    public function activeSectorCodes($classificationSystem = null)
    {
        $classificationSystem = $this->classificationSystem($classificationSystem);

        return DB::table($this->sectorsTable())
            ->where('classification_system', $classificationSystem)
            ->where('is_active', 1)
            ->pluck('sector_code')
            ->map(function ($code) {
                return strtoupper(trim((string) $code));
            })
            ->values()
            ->all();
    }

    public function resolveSectorCodesForTickerIds(array $tickerIds, $tradeDate, $classificationSystem = null)
    {
        $contexts = $this->resolveSectorContextForTickerIds($tickerIds, $tradeDate, $classificationSystem);
        $resolved = [];

        foreach ($contexts as $tickerId => $context) {
            $resolved[$tickerId] = $context['sector_code'];
        }

        return $resolved;
    }

    public function resolveSectorContextForTickerIds(array $tickerIds, $tradeDate, $classificationSystem = null)
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        $tickerIds = array_values(array_filter($tickerIds, function ($tickerId) {
            return $tickerId > 0;
        }));

        if (empty($tickerIds)) {
            return [];
        }

        $classificationSystem = $this->classificationSystem($classificationSystem);

        $rows = DB::table($this->membershipsTable().' as member')
            ->join($this->sectorsTable().' as sector', function ($join) use ($classificationSystem) {
                $join->on('sector.sector_code', '=', 'member.sector_code')
                    ->where('sector.classification_system', $classificationSystem)
                    ->where('sector.is_active', 1);
            })
            ->select('member.ticker_id', 'member.sector_code', 'sector.sector_index_code', 'member.effective_from', 'member.membership_id')
            ->whereIn('member.ticker_id', $tickerIds)
            ->where('member.classification_system', $classificationSystem)
            ->where('member.effective_from', '<=', $tradeDate)
            ->where(function ($query) use ($tradeDate) {
                $query->whereNull('member.effective_to')
                    ->orWhere('member.effective_to', '>=', $tradeDate);
            })
            ->orderBy('member.ticker_id')
            ->orderBy('member.effective_from', 'desc')
            ->orderBy('member.membership_id', 'desc')
            ->get();

        $resolved = [];
        foreach ($rows as $row) {
            $tickerId = (int) $row->ticker_id;
            if (isset($resolved[$tickerId])) {
                continue;
            }

            $sectorIndexCode = $row->sector_index_code !== null
                ? strtoupper(trim((string) $row->sector_index_code))
                : null;

            $resolved[$tickerId] = [
                'sector_code' => strtoupper(trim((string) $row->sector_code)),
                'sector_index_code' => $sectorIndexCode !== '' ? $sectorIndexCode : null,
            ];
        }

        return $resolved;
    }

    public function upsertMembership($tickerId, $sectorCode, $effectiveFrom, $effectiveTo = null, $sourceName = null, $sourceRef = null, $classificationSystem = null)
    {
        $classificationSystem = $this->classificationSystem($classificationSystem);
        $now = date('Y-m-d H:i:s');

        return DB::table($this->membershipsTable())->updateOrInsert(
            [
                'ticker_id' => (int) $tickerId,
                'classification_system' => $classificationSystem,
                'effective_from' => $effectiveFrom,
            ],
            [
                'sector_code' => strtoupper(trim((string) $sectorCode)),
                'effective_to' => $effectiveTo ?: null,
                'source_name' => $sourceName ?: null,
                'source_ref' => $sourceRef ?: null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    private function classificationSystem($classificationSystem)
    {
        $value = $classificationSystem ?: config('market_data.sectors.classification_system', 'IDX-IC');

        return strtoupper(trim((string) $value));
    }

    private function sectorsTable()
    {
        return config('market_data.sectors.table', 'market_data_sectors');
    }

    private function membershipsTable()
    {
        return config('market_data.sectors.membership_table', 'ticker_sector_memberships');
    }
}
