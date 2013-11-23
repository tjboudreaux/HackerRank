<?php
/**
 * @link https://www.hackerrank.com/challenges/botclean
 */

/** My Code **/
class Bot 
{
	private $positionX;
	private $positionY;
	
	public function __construct(&$x, &$y)
	{
		$this->positionX = $x;
		$this->positionY = $y;
	}
	
	public function moveLeft()
	{
		$this->positionX--;
	}
	
	public function moveRight()
	{
		$this->positionX++;
	}
	
	public function moveUp()
	{
		$this->positionY--;
	}
	
	public function moveDown()
	{
		$this->positionY++;
	}
	
	public function getX()
	{
		return $this->positionX;
	}
	
	public function getY()
	{
		return $this->positionY;
	}
	
}

class Board
{
	const STATE_DIRTY = 'd';
	const STATE_BOT	  = 'b';
	const STATE_CLEAN = '-';
	
	private $board;
	
	public function __construct($board, $print = false)
	{
		$result = array();
		foreach ($board as $row)
		{
			$result[] = str_split($row);		
		}
		$this->board = $result;
	}
	
	public function getMaxX()
	{
		return count($this->board[0]) - 1;
	}
	
	public function getMaxY()
	{
		return count($this->board) - 1;
	}
	
	public function setState($x,$y,$state)
	{
		$this->board[$y][$x] = $state;
	}
	
	public function getState($x,$y)
	{
		if ($this->getMaxX() >= $x && 
		    $this->getMaxY() >= $y)
		{
			
			return $this->board[$y][$x];
		}
	}
	
	public function isClean()
	{
		foreach($this->board as $row)
		{
			if (in_array(self::STATE_DIRTY, $row))
			{
				return false;
			}
		}
		
		return true;
	}
	
	public function closestDirtyTile($x, $y)
	{
		$closestDistance = 1000000;
		
		$closestTile = array('x'=>0, 'y'=>0);
		foreach($this->board as $boardY => $row)
		{

			foreach($row as $boardX => $tile)
			{
				if ($tile == Board::STATE_DIRTY)
				{
					$distance = abs($boardX - $x) + abs($boardY - $y);
					if ($distance <= $closestDistance)
					{
						$closestTile['x'] = $boardX;
						$closestTile['y'] = $boardY;
						$closestDistance = $distance;
					}
				}
			}
		}
		return $closestTile;
	}
	
	public function getBoard()
	{
		$result = array();
		foreach ($this->board as $row)
		{
			$result[] = implode("",$row); 
		}
		return $result;
	}
}

class Turn
{
	const OUTPUT_UP    = 'UP';
	const OUTPUT_DOWN  = 'DOWN';
	const OUTPUT_LEFT  = 'LEFT';
	const OUTPUT_RIGHT = 'RIGHT';
	const OUTPUT_CLEAN = 'CLEAN';
	
	private $bot;
	private $board;
	private $heuristic;
	
	public function __construct(Bot $bot, Board $board, BotHeuristic $heuristic) 
	{
		$this->bot = $bot;
		$this->board = $board;
		$this->heuristic = $heuristic;
	}
	
	public function canMoveUp()
	{
		return $this->bot->getY() > 0;
	}
	
	public function canMoveDown()
	{
		return $this->bot->getY() < $this->board->getMaxY();
	}
	
	public function canMoveLeft()
	{
		return $this->bot->getX() > 0;
	}
	
	public function canMoveRight()
	{
		return $this->bot->getX() < $this->board->getMaxX();
	}
	
	public function move()
	{
		$this->heuristic->move($this->bot, $this->board, $this);
	}
}

interface BotHeuristic
{
	public function move(Bot $bot, Board $board, Turn $turn);
}

class BasicHeuristic implements BotHeuristic
{

	public function move(Bot $bot, Board $board, Turn $turn)
	{

		$closestTile = $board->closestDirtyTile($bot->getX(), $bot->getY());
		
		if ($board->getState($bot->getX(), $bot->getY()) == $board::STATE_DIRTY)
		{
			echo $turn::OUTPUT_CLEAN;
			$board->setState($bot->getX(), $bot->getY(), $board::STATE_BOT);
			
		}else if ($turn->canMoveUp() && $closestTile['y'] < $bot->getY())
		{
			echo $turn::OUTPUT_UP;
			$board->setState($bot->getX(), $bot->getY(), $board::STATE_CLEAN);
			$bot->moveUp();
			
		}else if ($turn->canMoveDown() && $closestTile['y'] > $bot->getY())
		{
			echo $turn::OUTPUT_DOWN;
			$board->setState($bot->getX(), $bot->getY(), $board::STATE_CLEAN);
			$bot->moveDown();
		}else if ($turn->canMoveLeft() && $closestTile['x'] < $bot->getX())
		{
			echo $turn::OUTPUT_LEFT;
			$board->setState($bot->getX(), $bot->getY(), $board::STATE_CLEAN);
			$bot->moveLeft();
		}else if ($turn->canMoveRight() && $closestTile['x'] > $bot->getX())
		{
			echo $turn::OUTPUT_RIGHT;
			$board->setState($bot->getX(), $bot->getY(), $board::STATE_CLEAN);
			$bot->moveRight();
		}else {
			throw new Exception("Invalid Move");
		}
	}
}

/* Head ends here */
function next_move(&$y, &$x, &$board) {

	$print = false;
	$x = (int) $x;
	$y = (int) $y;
	
	$boardObj = new Board($board);
	$bot = new Bot($x, $y);
	$turn = new Turn($bot, $boardObj, new BasicHeuristic);
	$turn->move();
	
	$board = $boardObj->getBoard();
	$x = $bot->getX();
	$y = $bot->getY();
    return 0;
}
/* Tail starts here */
$fp = fopen("php://stdin", "r");

$temp = fgets($fp);              //moves made by the second player
$position = split(' ',$temp);

$board = array();

for ($i=0;$i<5;$i++) {
    fscanf($fp, "%s", $board[$i]);
}

next_move($position[0], $position[1], $board);
?>