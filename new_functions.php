﻿<?php
/*
	2014.07.10 
	검색후 url 파싱 테스트 php 파일
	안계완 만듦.
*/
include('parser.php');

function parsing($url, $pattern)
{
	if($url != '')
	{
		$iup = new HTMLParser();
		$iup->setUrl($url);
	
		foreach($pattern as $upgPatterns)
		{
			$iup->addPattern($upgPatterns);
		}
		$result = $iup->getResult();
	}
	else
	{
		$result = false;
	}
	
	return $result;
}

function calcPage($url)
{
	$totalPages = 0;
	//총 게시글 건수를 얻기 위한 패턴 추가
	$patterns = array('/<span[^>]*class=\"f_nb f_l\">1-10 \/(.*)건/');
	//파싱
	$result = parsing($url, $patterns);	
	
	$totalPages = $result[0][0][0]/10 + 1;

	return $totalPages;
}

function parsingPage($url)
{
	$patterns = array('/<a[^>]*class=\"f_link_bu f_l\" href=http://(.*)>/');
	
	$result = parsing($url, $patterns);
	
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
	if($url != '')
	{
		$patterns = array(
											'/htmlEntityDecode\(\'(.*)\'/', 
											'/class=\"txt_owner\">(.*)</', 
											'/class=\"num_info\">(.*)</', 
											'/<div id=\"article\">(.*)<\/span><\/p>/s' 
											);
	
		$result = parsing($url, $patterns);
		
		$p_title = $result[0][0][0];
		$p_Writer = $result[1][0][0];
		$p_Date = $result[2][0][0];
		$p_Hits = $result[2][0][1];
	
		$result[3][0][0] = strip_tags($result[3][0][0], '<br>');
		$result[3][0][0] = preg_replace('/(<br \/><br \/>)/','' ,$result[3][0][0]);

		//결과를 보기좋게 출력
		echo '<pre style="text-align:left">';
		print_r($result);
		echo '</pre>';
	}
}