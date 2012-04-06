<?php
/**
 * Created by JetBrains PhpStorm.
 * User: HuzZzo
 * Date: 20.03.12
 * Time: 22:06
 * To change this template use File | Settings | File Templates.
 */
class calculus
{
    //Бинарные операторы
    //Порядок следования операндов важен
    private $_binaryOperatios = array('+','-','*','/');

    //Унарные операторы
    private $_unaryOperations = array('-','+');

     //Вычисление позиции бинарного операнда
    private function getBinaryOperandPosition($sourceString,$binaryOperand)
    {
        $operandPososition=strrpos($sourceString,$binaryOperand);
        if (in_array($binaryOperand, $this->_unaryOperations) && $operandPososition - 1 > -1) {
            if (!is_numeric($sourceString[$operandPososition-1])){
                return false;
            }
        }
        return $operandPososition;
    }

    //Получение левой части выражения
    private function getExpressionLeftPart($sourceString,$operandPosition)
    {
        return substr($sourceString,0,$operandPosition);
    }

    //Получание правой части выражения
    private function getExpressionRightPart($sourceString,$operandPosition)
    {
        return substr($sourceString,$operandPosition+1,strlen($sourceString)-$operandPosition+1);
    }

    //Расчет значения бинарной операции
    private function getExpressionResult($leftPart,$rightPart,$operand)
    {
        if (!is_numeric($rightPart)) {
            throw new Exception('Error in right part');
        }
        if (!is_numeric($leftPart)) {
            throw new Exception('Error in left part');
        }
        switch ($operand) {
            case '+':
                return $leftPart+$rightPart;
            case '-':
                return $leftPart-$rightPart;
            case '*':
                return $leftPart*$rightPart;
            case '/':
               if ($rightPart == '0') {
                   throw new Exception('Division by zero');
               }
               return $leftPart/$rightPart;
            default:
                throw new Exception('Expression has no proceed');
        }
    }

    //Расчет значения выражения
    public function getAnswer($sourceExpression)
    {
       //если передана пустая строка
       if ($sourceExpression=='') {
           throw new Exception('Please enter expression in to inputbox');
       }
       //еcли строка - число, то возвращаем его
       if (is_numeric($sourceExpression)) {
           return $sourceExpression;
       }

       //Обработка операций не описывающихся базовой логикой построения дерева
       foreach (get_class_methods('expmodifi') as $modificator) {
           $modificatorExpression=expmodifi::$modificator($sourceExpression);
           if ($modificatorExpression) {
               // Если результатом операции является выражение, требующее пересчета
               if ($modificatorExpression['recursive']) {
                   $recursiveModificatorExpressionResult=$this->getAnswer($modificatorExpression['result']);
                   $sourceExpression=substr_replace($sourceExpression,$recursiveModificatorExpressionResult,
                                                    $modificatorExpression['startPosition'],
                                                    $modificatorExpression['cursorPosition']-
                                                    $modificatorExpression['startPosition']);
                   return $this->getAnswer($sourceExpression);
               }
               //Заменяем строку на результат выражения
               $sourceExpression=substr_replace($sourceExpression,$modificatorExpression['result'],
                                               $modificatorExpression['startPosition'],
                                               $modificatorExpression['cursorPosition']-
                                               $modificatorExpression['startPosition']);
               return $this->getAnswer($sourceExpression);
           }

       }

       //Базовые арифметические операции
       foreach ($this->_binaryOperatios as $binaryOperand) {
           $operatorPosition=$this->getBinaryOperandPosition($sourceExpression,$binaryOperand);
           if ($operatorPosition) {
               if ($operatorPosition==0 or $operatorPosition==strlen($sourceExpression)-1) {
                  throw new Exception('syntaxis error or modificator error');
               }
               else {
                   $left=$this->getAnswer($this->getExpressionLeftPart($sourceExpression,$operatorPosition));
                   $right=$this->getAnswer($this->getExpressionRightPart($sourceExpression,$operatorPosition));
                   //var_dump($right);
                   //die;
                   return $this->getExpressionResult($left,$right,$binaryOperand);
                   }
               }
           }

        throw new Exception('Expression has no proceed');
   }
}
