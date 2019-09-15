<?php
/**
 * AVOLUTIONS
 * 
 * Just another open source PHP framework.
 * 
 * @author		Alexander Vogt <alexander.vogt@avolutions.de>
 * @copyright	2019 avolutions (http://avolutions.de)
 * @license		MIT License (https://opensource.org/licenses/MIT)
 * @link		https://github.com/avolutions/avolutions
 */

namespace core\routing;

/**
 * Router class
 * 
 * The Router class find the matching Route for the url of the Request.
 *
 * @package		core
 * @author		Alexander Vogt <alexander.vogt@avolutions.de>
 */
class Router
{
	/**
	 * findRoute
	 * 
	 * Finds the matching Route from the RouteCollection by the passed uri/path and method.
	 * 
	 * @param string $path The requested uri/path
	 * @param string $method The method of the request
	 *
	 * @return object The matched Route object with final controller-/action names and parameter values. 
	 */
	public static function findRoute($path, $method) {
		$RouteCollection = RouteCollection::getInstance();
		$MatchedRoute = null;
				
		foreach ($RouteCollection->getAllByMethod($method) as $Route) {
			if (preg_match(self::getRegularExpression($Route), $path, $matches)) {
								
				$explodedUrl = explode('/', $Route->url);	
				
				$controllerName = self::getKeywordValue($matches, $explodedUrl, 'controller');
				$actionName = self::getKeywordValue($matches, $explodedUrl, 'action');
				
				$MatchedRoute = $Route;
				if($controllerName) {				
					$MatchedRoute->controllerName = $controllerName;	
				}
				if($actionName) {				
					$MatchedRoute->actionName = $actionName;	
				}
				$MatchedRoute->parameters = self::getParameterValues($matches, $explodedUrl, $MatchedRoute->parameters);
				
				break;
			}
		}	
		
		return $MatchedRoute;
	}	
	
	
	/**
	 * getRegularExpression
	 * 
	 * Returns the regular expression to match the given Route.
	 * 
	 * @param object $Route The Route object to build the expression for.
	 *
	 * @return string The regular expression to match the url of the Route. 
	 */
	private static function getRegularExpression($Route) {
		$startDelimiter = '/^';
		$endDelimiter = '$/';
		
		$controllerExpression = '([a-z]*)';
		$actionExpression = '([a-z]*)';
		
		$expression = $Route->url;
		$expression = str_replace('/', '\/', $expression);
		
		$expression = str_replace('<controller>', $controllerExpression, $expression);
		$expression = str_replace('<action>', $actionExpression, $expression);
		
		foreach($Route->parameters as $parameterName => $parameterValues) {
			$parameterExpression = '(';
			$parameterExpression .= $parameterValues["format"];
			if(isset($parameterValues["optional"]) && $parameterValues["optional"]) {
				$parameterExpression .= '?';
			}
			$parameterExpression .= ')';
			
			$expression = str_replace('<'.$parameterName.'>', $parameterExpression, $expression);
		}
		
		$expression = $startDelimiter.$expression.$endDelimiter;
				
		return $expression;
	}
	
	
	/**
	 * getKeywordValue
	 * 
	 * Returns the value of a given keyword from the url of the matched Route.
	 * 
	 * @param array $matches Array with the exploded url of the request.
	 * @param array $explodedUrl Array with the exploded url of the route.
	 * @param string $keyword Name of the keyword.
	 *
	 * @return mixed The value of the keyword from the url or false if nothing found. 
	 */
	private static function getKeywordValue($matches, $explodedUrl, $keyword) {
		$keywordIndex = array_search('<'.$keyword.'>', $explodedUrl); 
		
		return $keywordIndex ? $matches[$keywordIndex] : false;	
	}
	
	
	/**
	 * getParameterValues
	 * 
	 * Returns an array with all parameters values from the url of the matched Route.
	 * 
	 * @param array $matches Array with the exploded url of the request.
	 * @param array $explodedUrl Array with the exploded url of the route.
	 * @param array $parameters Array with the parameters of the route.
	 *
	 * @return array An array with all parameter values.
	 */
	private static function getParameterValues($matches, $explodedUrl, $parameters) {
		$parameterValues = array();
	
		foreach($parameters as $parameterName => $parameterOptions) {
			$value = self::getKeywordValue($matches, $explodedUrl, $parameterName);
			
			if($value) {
				$parameterValues[] = $value;
			} else {
				if(isset($parameterOptions["optional"]) && $parameterOptions["optional"]) {
					if(isset($parameterOptions["default"])) {
						$parameterValues[] = $parameterOptions["default"];
					}
				}				
			}
		}
		
		return $parameterValues;
	}
}
?>