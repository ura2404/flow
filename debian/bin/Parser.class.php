<?php
define('DX',20);
define('DY',50);
define('MIST',3);
define('PERC',10);	// %, минимальный уровень длины участка измерений 

define('INEXT','log');
define('OUTEXT','pre');
define('SKIEXT','ski');

class Parser {
    protected $Flow = [];
    protected $Areas = [];
    protected $FileIn;

    // --- --- --- --- ---
    function __construct($fileIn){
        echo $fileIn."\n";
        $this->FileIn = $fileIn;
        if(self::checkExt($fileIn,INEXT)) $this->doIt($fileIn);
    }

    // --- --- --- --- ---
    protected function readMyFlow($fileIn){
        $Arr = [];
        $Prev = 0;

        if(!is_readable($fileIn) || !filesize($fileIn)) return;

        if(!($Fin = fopen($fileIn,'r'))) return;
        while($Line = fgets($Fin)){
            $TS = trim(substr($Line,0,strrpos($Line,' ')));
            $Data = intval(trim(substr($Line,strrpos($Line,' ')+1)));
            $this->Flow[] = [
                'ts' => $TS,
                'data' => $Data,
                'delta' => abs($Data-$Prev)
            ];
            $Prev = $Data;
        }
    }

    // --- --- --- --- ---
    protected function getMyAreas(){
        $Size = count($this->Flow);
        $I = 1;
        $Begin = $End = $Count = $Min = $Max = $Mid = 0;

        array_map(function($key,$value) use($Size,&$I,&$Begin,&$End,&$Count,&$Min,&$Max,&$Mid){
            $Ts = $value['ts'];
            $Value = $value['data'];
            $Delta = $value['delta'];

            if(!$Begin && $Delta <= DY){
                $Begin = $key;
                $Min = $Value;
            }

	    if( ($Begin && $I == $Size) || ($Begin && $Delta > DY)){
		$End = ($I == $Size) ? $key : $key - 1;
		$AreaSize = round(($End-$Begin)/$Size*100,2,PHP_ROUND_HALF_UP);

		$this->Areas[] = [
		    'ts' => $Ts,
		    'begin' => $Begin,
		    'end' => $End,
		    'size' => $this->areaSize($Begin,$End,$Size),
		    'min' => $Min,
		    'max' => $Max,
		    'mid' => round(($Min + $Max) / 2,0,PHP_ROUND_HALF_UP),
		];

		$Begin = $End = $Count = $Min = $Max = $Mid = 0;
	    }

	    if($Begin){
		$Min = min($Min,$Value);
		$Max = max($Max,$Value);
		$Count++;
	    }

	    $I++;
	},array_keys($this->Flow),array_values($this->Flow));

	$this->Areas = array_filter($this->Areas,function($value){
	    return $value['size'] < PERC ? false : true;
	});
    }

    // --- --- --- --- ---
    protected function splitMyAreas(){
        if(!count($this->Areas)) return [];
        $Size = count($this->Flow);

        $Arr = [array_shift($this->Areas)];
        array_map(function($area) use($Size,&$Arr){
            $Index = count($Arr)-1;
            $Prev = $Arr[$Index];
            if(abs($Prev['mid']-$area['mid']) <= DY){
                $Arr[$Index] = [
                    'ts' => $Prev['ts'],
                    'begin' => $Prev['begin'],
                    'end' => $area['end'],
                    'size' => $this->areaSize($Prev['begin'],$area['end'],$Size),
                    'min' => min($Prev['min'],$area['min']),
                    'max' => max($Prev['max'],$area['max']),
                    'mid' => round(($Prev['mid'] + $area['mid']) / 2,0,PHP_ROUND_HALF_UP),
                ];
            }
            else{
                $Arr[] = $area;
            }
        },$this->Areas);

        $this->Areas = $Arr;
    }

    // --- --- --- --- ---
    // percent of real data
    protected function areaSize($begin,$end,$size){
        return round(($end-$begin)/$size*100,2,PHP_ROUND_HALF_UP);
    }

    // --- --- --- --- ---
    protected function saveMyAreas(){
        if(!count($this->Areas)){
            $FileOut = dirname($this->FileIn) .'/'. basename($this->FileIn,'.'.INEXT).'.'.SKIEXT;
            file_put_contents($FileOut,null);
        }
        else{
            $FileOut = dirname($this->FileIn) .'/'. basename($this->FileIn,'.'.INEXT).'.'.OUTEXT;

            $Arr = array_map(function($area){
                return $area['ts'] .' : '. $area['mid'] .' ( '. $area['min'] .' - '. $area['max'] .' )';
            },$this->Areas);
            file_put_contents($FileOut,implode("\n",$Arr)."\n");
        }
    }

    // --- --- --- --- ---
    // --- --- --- --- ---
    // --- --- --- --- ---
    public function doIt($filePath){
	$this->readMyFlow($filePath);
	//print_r($this->Flow);

	$this->getMyAreas();
	//print_r($this->Areas);
	//echo count($this->Areas)."\n";

	$this->splitMyAreas();
	//print_r($this->Areas);
	//echo count($this->Areas)."\n";

	$this->saveMyAreas();
    }

    // --- --- --- --- ---
    // --- --- --- --- ---
    // --- --- --- --- ---
    static function checkExt($file,$ext){
        $Name = basename($file);
        if(($Pos = strrpos($Name,'.')) === false) return false;
        else{
            return substr($Name,$Pos+1) == $ext;
        }
    }

    // --- --- --- --- ---
    static function get($in){
        $Files = scandir($in);
        $Files = array_filter($Files,function($file){
            return ($file === '.' || $file === '..' || self::checkExt($file,INEXT) === false) ? false : true;
        });

        foreach($Files as $file){
            new Parser($in .'/'. $file);
        }
    }
}
?>