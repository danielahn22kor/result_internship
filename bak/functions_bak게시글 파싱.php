<?php
/*
	2014.07.10 
	검색후 url 파싱 테스트 php 파일
	안계완 만듦.
*/
include('parser.php');

function calcPage($url)
{
	$totalPages = 0;
	if($url != '')
	{
		$iup = new HTMLParser();
		$iup->setUrl($url);
	
		//총 게시글 건수를 얻기위한 패턴 추가
		$iup->addPattern('/<span[^>]*class=\"f_nb f_l\">1-10 \/(.*)건/' );
	
		//파싱결과를 돌려줌
		$result = $iup->getResult();
	
		//총 페이지 구함
		$totalPages = $result[0][0][0]/10 + 1;
	}
	return $totalPages;
}

function parsingPage($url)
{
	$iup = new HTMLParser();
	$iup->setUrl($url);
	
	//검색된 글 URL들을 얻기위해 패턴 추가'/<img[^>]*src=["\']?([^>"\']+)["\']?[^>]*>/'
	$iup->addPattern('/<a[^>]*class=\"f_link_bu f_l\" href=["\']?([^>"\']+)["\']?[^>]*>/');
	
	//파싱
	$result = $iup->getResult();
	
	return $result;
}

function overlapPage($url)
{
	//전역변수 사용
	global $compareStr;
	//값이 같으면 true
	if($url == $compareStr)
	{
		return true;
	}
	else
	{
		//다르면 url로 기존것을 덮고 false 반환
		$compareStr = $url;
		return false;
	}
}

//검색의 기본 url 생성
function makeBasicUrl($searcher)
{
	$basicUrl = 'http://search.daum.net/search?w=cafe&q=';
	$basicUrl .= $searcher;
	$basicUrl .= '&m=board&ASearchType=1&lpp=10&rlang=0&period=u&p=';
	return $basicUrl; 
}

//입력받은 기간 설정
function makeDateUrl($searchRange)
{
	$searchDate = '&sd=';
	$searchDate .= $searchRange[0];
	$searchDate .= '000000&ed=';
	$searchDate .= $searchRange[1];
	$searchDate .= '235959&page=1';
	return $searchDate;
}

//최종 url 조합
function makeUrl($basicUrl, $page, $dateUrl)
{
	$url = $basicUrl;
	$url .= $page;
	$url .= $dateUrl;
	return $url;
}

//만들 파일 path 생성
function makePath($searcher)
{
	$newTextPath = '/usr/local/apache/htdocs/danAhn/';
	$newTextPath .= 'test';
	$newTextPath .= '.txt';
	return $newTextPath;

}

//url을 파일에 쓰는 함수
function writeUrl($basicUrl, $dateUrl, $searcher)
{
	//초기 url 생성
	$url = makeUrl($basicUrl, 1, $dateUrl);
	
	//총 페이지 계산
	$totalPage = calcPage($url);
	$newTextPath = makePath($searcher);
	
	$fd = fopen($newTextPath, 'w+');
		
	//쓰기용 버퍼 생성
	$fwrireBuff = "";
	
	for($page = "1"; $page < $totalPage + "1"; $page++)
	{
		$url = makeUrl($basicUrl, $page, $dateUrl);
		
		//url을 얻어옴
		$result = parsingPage($url);
	
		//중복 방지를 위한 비교
		if(!overlapPage($result[0][0][1]))
		{
			//result들을 쓰기 시작
			for($resultCnt = 1; $resultCnt < 11; $resultCnt++)
			{
				//쓰기용 버퍼를 이용해 url을 씀
				if($result[0][0][$resultCnt] == NULL)
					break;
				$fWriteBuff .= $result[0][0][$resultCnt];
				$fWriteBuff .= "\n";
			}
		}
		else
		{
			break;
		}
	
	}
	fwrite($fd, $fWriteBuff);
	fclose($fd);
}

//가장 초기에 실행되는 함수 url들을 다 얻어옴
function getUrl($searchRange, $searcher)
{
	
	$basicUrl = makeBasicUrl($searcher);
	$dateUrl = makeDateUrl($searchRange);
	
	writeUrl($basicUrl, $dateUrl, $searcher);
}

function parsingText($url)
{
	<span class="b">
	if($url != '')
	{
		$iup = new HTMLParser();
		$iup->setUrl($url);
	
		//타이틀 파싱[0][0][0]에 존재
		$iup->addPattern('/<span[^>]*class=\"b\">(.*)</span>/' );
		
		//글쓴이 파싱 [1][0][0]에 존재
		$iup->addPattern('/class=\"txt_point p11\">(.*)</a>/' );
		
		//내용 파싱 [2][0][0]~ 존재
		<xmp id="template_xmp" name="template_xmp" style="display:none;">
		$iup->addPattern('/<xmp id=\"templage_xmp\" name=\"template_xmp\" style=\"display\:none\;\">(.*)<!-- -->/' );
	
		//파싱결과를 돌려줌
		$result = $iup->getResult();
	
		//결과를 보기좋게 출력
		//총 페이지 구함
		print_r($result);
	}
	return $totalPages;
}