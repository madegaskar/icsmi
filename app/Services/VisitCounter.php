<?php

namespace App\Services;

use App\Models\PageVisit;
use Illuminate\Database\UniqueConstraintViolationException;
use App\Support\CountryFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Counts public page views per day and country. Only the country code is kept, never the IP address.
 */
final class VisitCounter
{
    private const COUNTRY_HEADER = 'CF-IPCountry';

    private const TOP_COUNTRIES = 8;

    /** Stored instead of NULL so the unique index also stops duplicate unknown rows. */
    private const UNKNOWN_COUNTRY = '';

    public function record(Request $request): void
    {
        $today = now()->toDateString();
        $country = $this->countryFrom($request);

        if ($this->todayRow($today, $country)->increment('count') === 0) {
            $this->startToday($today, $country);
        }
    }

    private function todayRow(string $today, string $country): Builder
    {
        return DB::table('page_visits')
            ->where('visited_on', $today)
            ->where('country_code', $country);
    }

    /**
     * Creates today's row for a country. If another request created it first, count into that row instead.
     */
    private function startToday(string $today, string $country): void
    {
        try {
            DB::table('page_visits')->insert(['visited_on' => $today, 'country_code' => $country, 'count' => 1]);
        } catch (UniqueConstraintViolationException) {
            $this->todayRow($today, $country)->increment('count');
        }
    }

    /**
     * @return array{total: int, countries: array<int, array{code: ?string, name: string, flag: string, count: int}>}
     */
    public function summary(): array
    {
        $rows = PageVisit::query()
            ->select('country_code', DB::raw('SUM(count) as visits'))
            ->groupBy('country_code')
            ->orderByDesc('visits')
            ->get();

        return [
            'total' => (int) $rows->sum('visits'),
            'countries' => $rows->take(self::TOP_COUNTRIES)->map(fn (PageVisit $row) => [
                'code' => $row->country_code === self::UNKNOWN_COUNTRY ? null : $row->country_code,
                'name' => CountryFlag::name($row->country_code),
                'flag' => CountryFlag::emoji($row->country_code),
                'count' => (int) $row->visits,
            ])->values()->all(),
        ];
    }

    private function countryFrom(Request $request): string
    {
        if (! $this->fromTrustedProxy($request)) {
            return self::UNKNOWN_COUNTRY;
        }

        $code = strtoupper((string) $request->header(self::COUNTRY_HEADER));

        return preg_match('/^[A-Z]{2}$/', $code) === 1 ? $code : self::UNKNOWN_COUNTRY;
    }

    /**
     * The country header is only trusted when the request arrives from a configured reverse proxy.
     */
    private function fromTrustedProxy(Request $request): bool
    {
        $proxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('trustedproxy.proxies')),
        )));
        $remoteAddress = (string) $request->server->get('REMOTE_ADDR');

        return $proxies !== [] && $remoteAddress !== '' && IpUtils::checkIp($remoteAddress, $proxies);
    }
}
