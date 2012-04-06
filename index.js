/**
 * Created by JetBrains WebStorm.
 * User: HuzZzo
 * Date: 15.03.12
 * Time: 21:52
 * To change this template use File | Settings | File Templates.
 */
//задаем автофокус в окне ввода
$("#iBox").focus();

function ResultButton_click ()
{
    $.getJSON('index.php?InputBox='+encodeURIComponent($("#iBox").val()),function(json) {
        $("#oBox").val(json.resp);
    });

}
//накладываем ограничение на ввод в текстбокс
$(document).ready(function()
    {
        $("#iBox").keydown(function(event)
        {
            if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 39 || event.keyCode == 37
                || event.keyCode == 110 || event.keyCode == 190 || event.keyCode == 187 || event.keyCode == 189 || event.keyCode == 191)
            {
                // let it happen, don't do anything
            }
            else
            {
                // Ensure that it is a number and stop the keypress
                if (!((event.keyCode >= 48 && event.keyCode <= 57 ) || (event.keyCode >= 96  && event.keyCode <= 105)))
                {
                    event.preventDefault();
                }
            }
        });
    });
function DigitButton(operation)
{
    var $str=$("#iBox").val();
    $("#iBox").val($str+operation);
}
function BackButton()
{
    var $str=$("#iBox").val();
    $("#iBox").val($str.substr(0,$str.length-1));
}