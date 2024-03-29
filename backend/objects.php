<?php
	//This is the RM object core, which is to be included with the functions page
?>
<?php
	class LogEntry
	{
		private $ip;
		private $time;
		private $unread;
		
		public function __construct($i,$t,$u)
		{
			$this->ip=$i;
			$this->time=$t;
			switch($u)
			{
				case 0:
				$this->unread='U';
				break;
				case 1:
				$this->unread='R';
				break;
				default:
				$this->unread='?';
				break;
			}
		}
		
		public function getIP()
		{
			return $this->ip;
		}
		public function getTime()
		{
			return $this->time;
		}
		public function getUnread()
		{
			return $this->unread;
		}
	}
	class SystemLogEntry extends LogEntry
	{
		private $page;
		private $text;
		
		public function __construct($i,$t,$p,$x,$u)
		{
			parent::__construct($i,$t,$u);
			$this->page=$p;
			$this->text=$x;
		}
		
		public function getPage()
		{
			return $this->page;
		}
		public function getText()
		{
			return $this->text;
		}
	}
	class ErrorLogEntry extends SystemLogEntry
	{
		private $error;
		
		public function __construct($i,$t,$p,$e,$x,$u)
		{
			parent::__construct($i,$t,$p,$x,$u);
			$this->error=$e;
		}
		
		public function getError()
		{
			return $this->error;
		}
	}
	class LoginLogEntry extends LogEntry
	{
		private $browser;
		private $status;
		
		public function __construct($i,$b,$t,$s,$u)
		{
			parent::__construct($i,$t,$u);
			$this->browser=$b;
			switch($s)
			{
				case 0:
				$this->status="FAILED";
				break;
				case 1:
				$this->status="SUCCEEDED";
				break;
				default:
				$this->status="INDETERMINATE";
				break;
			}
		}
		
		public function getBrowser()
		{
			return $this->browser;
		}
		
		public function getStatus()
		{
			return $this->status;
		}
	}
	
	class Song
	{
		private $id;
		private $list;
		private $details;
		private $added;
		private $count;
		private $lastreq;
		
		public function __construct($i,$l,$d,$a,$c,$r)
		{
			$this->id=$i;
			$this->list=$l;
			$this->added=$a;
			$this->count=$c;
			$this->lastreq=$r;
			$d=explode("|",$d);
			for($x=0;$x<count($d);$x++)
			{
				$d[$x]=explode("=",$d[$x]);
			}
			foreach($d as $x)
			{
				$this->details[$x[0]]=$x[1];
			}
		}
		
		public function getID()
		{
			return $this->id;
		}
		public function getList()
		{
			return $this->list;
		}
		public function getDetails($item=NULL)
		{
			if($item !== NULL)
			{
				if(!empty($this->details[$item]))
				{
					return $this->details[$item];
				}
				return "";
			}
			return $this->details;
		}
		public function getRawDetails()
		{
			$out=array();
			foreach(array_keys($this->details) as $key)
			{
				$out[]="$key=" . $this->details[$key];
			}
			return implode("|",$out);
		}
		public function getAdded()
		{
			return $this->added;
		}
		public function getCount()
		{
			return $this->count;
		}
		public function getLastReq()
		{
			return $this->lastreq;
		}
	}
	
	class Request
	{
		private $id;
		private $name;
		private $ip;
		private $mode;
		private $songid;
		private $songtext;
		private $custom;
		private $time;
		private $status;
		private $comment;
		private $response;
		
		public function __construct($d,$n,$i,$m,$s,$e,$u,$o,$r)
		{
			$this->id=$d;
			$this->name=$n;
			$this->ip=$i;
			$this->mode=$m;
			$this->time=$e;
			$this->status=$u;
			$this->comment=$o;
			$this->response=$r;
			switch($m)
			{
				case 0:
				$this->songid=$s;
				break;
				
				case 1:
				$this->songtext=$s;
				break;
				
				case 2:
				$this->custom=$s;
				break;
				
				default:
				$this->mode=2;
				$this->custom=$s;
				break;
			}
		}
		
		public function getID()
		{
			return $this->id;
		}
		public function getName()
		{
			return $this->name;
		}
		public function getIP()
		{
			return $this->ip;
		}
		public function getMode()
		{
			return $this->mode;
		}
		public function getSong($item=NULL)
		{
			switch($this->mode)
			{
				case 0:
				return $this->songid;
				break;
				
				case 1:
				return $this->songtext;
				break;
				
				case 2:
				return $this->custom;
				break;
				
				default:
				return "INDETERMINATE";
				break;
			}
		}
		public function getTime()
		{
			return $this->time;
		}
		public function getStatus()
		{
			return $this->status;
		}
		public function getComment()
		{
			return $this->comment;
		}
		public function getResponse()
		{
			return $this->response;
		}
	}
	
	class Version
	{
		private $buildcode;
		private $major;
		private $minor;
		private $revision;
		private $tag;
		private $release;
		private $installed;
		
		public function __construct($b,$j,$n,$r,$t,$d,$i)
		{
			$this->buildcode=$b;
			$this->major=$j;
			$this->minor=$n;
			$this->revision=$r;
			$this->tag=$t;
			$this->release=$d;
			$this->installed=$i;
		}
		
		public function getBuildCode()
		{
			return $this->buildcode;
		}
		public function getMajor()
		{
			return $this->major;
		}
		public function getMinor()
		{
			return $this->minor;
		}
		public function getRevision()
		{
			return $this->revision;
		}
		public function getTag()
		{
			return $this->tag;
		}
		public function getRelease()
		{
			return $this->release;
		}
		public function getInstalled()
		{
			return $this->installed;
		}
	}
	
	class Report
	{
		private $id;
		private $ip;
		private $request;
		private $reason;
		private $unread;
		
		public function __construct($d,$p,$r,$t,$u)
		{
			$this->id=$d;
			$this->ip=$p;
			$this->request=$r;
			$this->reason=$t;
			switch($u)
			{
				case 0:
				$this->unread='U';
				break;
				case 1:
				$this->unread='R';
				break;
				default:
				$this->unread='?';
				break;
			}
		}
		
		public function getID()
		{
			return $this->id;
		}
		public function getIP()
		{
			return $this->ip;
		}
		public function getRequest()
		{
			return $this->request;
		}
		public function getReason()
		{
			return $this->reason;
		}
		public function getUnread()
		{
			return $this->unread;
		}
	}
	
	class Ban
	{
		private $id;
		private $item;
		private $date;
		private $until;
		private $reason;
		
		public function __construct($i,$t,$d,$u,$r)
		{
			$this->id=$i;
			$this->item=$t;
			$this->date=$d;
			$this->until=$u;
			$this->reason=$r;
		}
		
		public function getID()
		{
			return $this->id;
		}
		public function getItem()
		{
			return $this->item;
		}
		public function getDate()
		{
			return $this->date;
		}
		public function getUntil()
		{
			return $this->until;
		}
		public function getReason()
		{
			return $this->reason;
		}
		
		public function isActive()
		{
			if($this->until == 0)
			{
				return true;
			}
			else
			{
				if($this->until > time())
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}
	
	class UpgradeReturn
	{
		private $code;
		private $success;
		private $fail;
		
		public function __construct($c,$s=0,$f=0)
		{
			$this->code=$c;
			$this->success=$s;
			$this->fail=$f;
		}
		
		public function getCode()
		{
			return $this->code;
		}
		public function getSuccess()
		{
			return $this->success;
		}
		public function getFail()
		{
			return $this->fail;
		}
	}
	
	class Setting
	{
		private $name;
		private $description;
		private $current;
		private $previous;
		private $default;
		
		public function __construct($n,$d,$s)
		{
			$this->name=$n;
			$this->description=$d;
			$this->current=$s;
			$this->previous="Not set";
			$this->default="Not set";
		}
		
		public function getName()
		{
			return $this->name;
		}
		public function getDescription()
		{
			return $this->description;
		}
		public function getCurrent()
		{
			return $this->current;
		}
		public function getPrevious()
		{
			return $this->previous;
		}
		public function getDefault()
		{
			return $this->default;
		}
		
		public function setPrevious($p)
		{
			$this->previous=$p;
		}
		public function setDefault($d)
		{
			$this->default=$d;
		}
	}
?>