<?php
/**
 * Набор модификаторов арифметического выражения, т.е. выражения не вписываются в общую логику.
 * Например, расчет идет справа налево или результат может быть посчитан без построения дерева.
 *
 * Методы должны возвращать массив:
 * 'result'=>'Результат операции'
 * 'startPosition'=>'Позиция символа, с которого нужно подменить строку (функцию, выражение) на результат'
 * 'cursorPosition'=>'Позиция символа, до которого нужно подменить строку (функцию, выражение) на результат'
 * 'recursive'=>boolean, требуется ли пересчет для возвращаемого результата (Если результатом является выражение)
 */
class expmodifi
{
    public static function openBrackers($sourceExpression)
    {
        /*
         Обработка выражения в скобках
        */
        $bracketStartPosition=strpos($sourceExpression,'(');
        $bracketCursorPosition=$bracketStartPosition;
        if ($bracketCursorPosition > -1) {
            //ищем закрывающу скобку
            $bracketCheckSumm=1;
            while ($bracketCheckSumm>0) {
                $bracketCursorPosition++;
                //Ошибка, если кол-во скобок не верное
                if ($bracketCursorPosition >= strlen($sourceExpression)) {
                    throw new Exception('Brackers inconsistency');
                }
                if (strcmp($sourceExpression[$bracketCursorPosition],')')==0) {
                    $bracketCheckSumm--;
                }
                if (strcmp($sourceExpression[$bracketCursorPosition],'(')==0) {
                    $bracketCheckSumm++;
                }
            }
            //Проверка "соседей" у скобок
            if ($bracketStartPosition>0) {
                if (is_numeric($sourceExpression[$bracketStartPosition-1])
                    or $sourceExpression[$bracketStartPosition-1]=='.') {
                    throw new Exception('Expected operator before "("');
                }
            }
            if ($bracketCursorPosition<strlen($sourceExpression)-1) {
                if (is_numeric($sourceExpression[$bracketCursorPosition+1])
                    or $sourceExpression[$bracketCursorPosition+1]=='.') {
                    throw new Exception('Expected operator after ")"');
                }

            }
            //Получаем выражение в скобках
            $inBracketExpression=substr($sourceExpression,$bracketStartPosition+1,
                                        $bracketCursorPosition-$bracketStartPosition-1);
            return array('result'=>$inBracketExpression, 'startPosition'=>$bracketStartPosition,
                         'cursorPosition'=>$bracketCursorPosition+1, 'recursive'=>1);
        }
        return false;
    }


    public static function replaceDoubleSigns($sourceExpression)
    {
        /*
         Замена двойных знаков
        */

        //Записи, подлежащие сокращению
        $doubleSigns = array('-+','+-','++','--');

        foreach ($doubleSigns as $doubleSign) {
            $startPosition=strpos($sourceExpression,$doubleSign);
            if ($startPosition>-1) {
                switch ($doubleSign) {
                    case '++':
                        return array('result'=>'+', 'startPosition'=>$startPosition, 'cursorPosition'=>$startPosition+2, 'recursive'=>0);
                    case '--':
                        return array('result'=>'+', 'startPosition'=>$startPosition, 'cursorPosition'=>$startPosition+2, 'recursive'=>0);
                    case '-+':
                        return array('result'=>'-', 'startPosition'=>$startPosition, 'cursorPosition'=>$startPosition+2, 'recursive'=>0);
                    case '+-':
                        return array('result'=>'-', 'startPosition'=>$startPosition, 'cursorPosition'=>$startPosition+2, 'recursive'=>0);
                    default:
                        throw new Exception('failed to make change a double sign in: ' . $sourceExpression);
                }
            }
        }
       return false;
    }

    public  static function getPow($sourceExpression)
    {
        /*
         Возведение в степень
        */
        $powerPosition=strpos($sourceExpression,'^');
        if ($powerPosition>-1) {

            //Некоректная запись
            if ($powerPosition==0) {
                throw new Exception('Operand expected on left side from "^"');
            }
            if ($powerPosition==strlen($sourceExpression)-1) {
                throw new Exception('Operand expected on right side from "^"');
            }

            //высчитываем левую часть, с учетом особенностей работы is_numeric();
            $powerLeftCursor=1;

            //Флаг поднят, если потребовалось модифицировать выражение, чтобы посчитать его значение
            $sourceExpressionModificatedFlag=0;
            do {
                $leftPart=substr($sourceExpression,$powerPosition-$powerLeftCursor,$powerLeftCursor);
                $powerLeftCursor++;
            } while (($powerPosition-$powerLeftCursor>=0)&&(is_numeric($leftPart)));

            if ($powerPosition-$powerLeftCursor>-1) {
                if (is_numeric($leftPart[0])) {
                    $sourceExpression=substr_replace($sourceExpression,$leftPart[1].'+',$powerPosition-$powerLeftCursor+2,1);
                    $sourceExpressionModificatedFlag=1;
                }
                $powerPosition=strpos($sourceExpression,'^');
                $leftPart=substr($sourceExpression,$powerPosition-$powerLeftCursor+2,strlen($leftPart)-1);
            }

            //Случай, когда оба условия выхода из цикла сработали
            if (!($powerPosition-$powerLeftCursor>-1)&&!(is_numeric($leftPart))) {
                $sourceExpression=substr_replace($sourceExpression,$leftPart[1].'+',$powerPosition-$powerLeftCursor+2,1);
                $sourceExpressionModificatedFlag=1;
                $powerPosition=strpos($sourceExpression,'^');
                $leftPart=substr($sourceExpression,$powerPosition-$powerLeftCursor+2,strlen($leftPart)-1);
            }
            if (!(is_numeric($leftPart))) {
                throw new Exception('Incorrect left part of "^" operation' );
            }

            //Разбор правой части
            $PowerPosition=strpos($sourceExpression,'^');
            $PowerRightCursor=1;
            $rightPart=substr($sourceExpression,$PowerPosition+1,$PowerRightCursor);
            if (($rightPart=='+') or ($rightPart=='-')) {
                $PowerRightCursor++;
                $rightPart=substr($sourceExpression,$PowerPosition+1,$PowerRightCursor);
            }
            while (($PowerPosition+$PowerRightCursor<=(strlen($sourceExpression))-1)&&(is_numeric($rightPart))) {
                $rightPart=substr($sourceExpression,$powerPosition+1,$PowerRightCursor);
                $PowerRightCursor++;
            }
            if ($PowerPosition+$PowerRightCursor<=(strlen($sourceExpression))-1) {
                $rightPart=substr($rightPart,0,strlen($rightPart)-1);
            }
            if (!(is_numeric($rightPart))) {
                throw new Exception('Incorrect right part of "^" operation');
            }
            //проверка результата вычислений
            $powResult=pow($leftPart,$rightPart);

            //Проверка полученного результата на допустимость
            if (is_infinite($powResult) or is_nan($powResult)) {
                throw new Exception('Expression: ' . $leftPart . '^' . $rightPart . ' = ' . $powResult);
            }
            return array('result'=>$powResult, 'startPosition'=>$PowerPosition-strlen($leftPart),
                         'cursorPosition'=>$PowerPosition+strlen($rightPart)+1-$sourceExpressionModificatedFlag, 'recursive'=>0);
        }
        return false;
    }

}
