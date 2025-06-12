<?php

class Glicko2Player {
    private float $rating;
    private float $rd;
    private float $volatility;

    const SCALE = 173.7178;

    public function __construct(float $rating = 1500, float $rd = 350, float $volatility = 0.06) {
        $this->rating = $rating;
        $this->rd = $rd;
        $this->volatility = $volatility;
    }

    public function getRating(): float { return $this->rating; }
    public function getRd(): float { return $this->rd; }
    public function getVolatility(): float { return $this->volatility; }

    public function setRating(float $r) { $this->rating = $r; }
    public function setRd(float $rd) { $this->rd = $rd; }
    public function setVolatility(float $v) { $this->volatility = $v; }

    // Conversão para escala Glicko-2
    public function getRatingGlicko(): float { return ($this->rating - 1500) / self::SCALE; }
    public function getRdGlicko(): float { return $this->rd / self::SCALE; }
}

class Glicko2 {
    const TAU = 0.5;
    private array $results = [];

    public function createPlayer(float $rating = 1500, float $rd = 350, float $volatility = 0.06): Glicko2Player {
        return new Glicko2Player($rating, $rd, $volatility);
    }

    // Adiciona resultado: player, oponente, score (1=vitória, 0=derrota)
    public function addResult(Glicko2Player $player, Glicko2Player $opponent, float $score) {
        $this->results[] = [$player, $opponent, $score];
    }

    // Atualiza ratings de todos os jogadores envolvidos
    public function updateRatings(array $players) {
        foreach ($players as $player) {
            $matches = [];
            foreach ($this->results as [$p, $opp, $score]) {
                if ($p === $player) {
                    $matches[] = ['opponent' => $opp, 'score' => $score];
                }
            }
            if (empty($matches)) continue;

            $mu = $player->getRatingGlicko();
            $phi = $player->getRdGlicko();
            $sigma = $player->getVolatility();

            // Cálculo do v e delta
            $vInv = 0.0;
            $deltaSum = 0.0;
            foreach ($matches as $m) {
                $mu_j = $m['opponent']->getRatingGlicko();
                $phi_j = $m['opponent']->getRdGlicko();
                $s = $m['score'];
                $g = 1 / sqrt(1 + 3 * $phi_j**2 / pi()**2);
                $E = 1 / (1 + exp(-$g * ($mu - $mu_j)));
                $vInv += ($g**2) * $E * (1 - $E);
                $deltaSum += $g * ($s - $E);
            }
            $v = 1 / $vInv;
            $delta = $v * $deltaSum;

            // Iteração para nova volatilidade
            $a = log($sigma**2);
            $A = $a;
            $B = ($delta**2 > $phi**2 + $v) ? log($delta**2 - $phi**2 - $v) : $a - self::TAU;
            $fA = self::f($A, $delta, $phi, $v, $a);
            $fB = self::f($B, $delta, $phi, $v, $a);

            while (abs($B - $A) > 0.000001) {
                $C = $A + ($A - $B) * $fA / ($fB - $fA);
                $fC = self::f($C, $delta, $phi, $v, $a);
                if ($fC * $fB < 0) {
                    $A = $B; $fA = $fB;
                } else {
                    $fA /= 2;
                }
                $B = $C; $fB = $fC;
            }
            $newSigma = exp($A / 2);

            // Atualiza rating e RD
            $phiStar = sqrt($phi**2 + $newSigma**2);
            $phiPrime = 1 / sqrt(1/$phiStar**2 + 1/$v);
            $muPrime = $mu + $phiPrime**2 * $deltaSum;

            $player->setRating($muPrime * Glicko2Player::SCALE + 1500);
            $player->setRd($phiPrime * Glicko2Player::SCALE);
            $player->setVolatility($newSigma);
        }
        $this->results = [];
    }

    private static function f($x, $delta, $phi, $v, $a): float {
        $ex = exp($x);
        $num = $ex * ($delta**2 - $phi**2 - $v - $ex);
        $den = 2 * ($phi**2 + $v + $ex)**2;
        return ($num / $den) - (($x - $a) / (self::TAU**2));
    }
}
