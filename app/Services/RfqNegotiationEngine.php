<?php

namespace App\Services;

use App\Models\P2pBid;
use App\Models\P2pRfq;
use InvalidArgumentException;

class RfqNegotiationEngine
{
    /**
     * Calculates weighted scores for all bids submitted to an RFQ and recommends the award winner.
     */
    public function evaluateBids(int $rfqId, float $technicalWeight = 0.6, float $commercialWeight = 0.4): ?P2pBid
    {
        if (abs(($technicalWeight + $commercialWeight) - 1.0) > 0.0001) {
            throw new InvalidArgumentException('Technical and commercial weights must sum exactly to 1.0.');
        }

        $rfq = P2pRfq::with('bids.vendor')->findOrFail($rfqId);

        if ($rfq->bids->isEmpty()) {
            return null;
        }

        // Identify lowest quote for commercial score base (relative scoring)
        $lowestQuote = (float) $rfq->bids->min('commercial_quote');
        if ($lowestQuote <= 0) {
            $lowestQuote = 1.00;
        }

        $bestBid = null;
        $highestScore = -1.0;

        foreach ($rfq->bids as $bid) {
            $techScoreRaw = (float) $bid->technical_score; // Out of 100
            $commQuoteRaw = (float) $bid->commercial_quote;

            // Commercial score = (Lowest Quote / Bid Quote) * 100
            $commScoreRaw = $commQuoteRaw > 0 ? ($lowestQuote / $commQuoteRaw) * 100.00 : 0.00;

            // Weighted score
            $finalScore = ($techScoreRaw * $technicalWeight) + ($commScoreRaw * $commercialWeight);

            if ($finalScore > $highestScore) {
                $highestScore = $finalScore;
                $bestBid = $bid;
            }
        }

        return $bestBid;
    }
}
