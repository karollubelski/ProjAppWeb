var computed = false;
var decimal = 0;

function convert(entryform, from, to) 
{
    from = from.selectedIndex;
    to = to.selectedIndex;
    entryform.form.display.value = (entryform.input.value * from[convertfrom].value / to[convertto].value);
}

function addChar(input, character) 
{
    if ((character == "." && decimal == 0) || character != ".")
    {
        if (input.value == "" || input.value == "0") 
        {
            input.value = character;
        } 
        else
        {
            input.value += character;
        }
        computed = true;
        if (character == ".")
        {
            decimal = 1;
        }
    }
}

function openVotchcom() 
{
    window.open("", "display window", "toolbar=no,directories=no,menubar=no");
}

function clear(form) 
{
    form.input.value = 0;
    form.display.value = 0;
    decimal = 0;
}

function changeBackground(hexNumber) {
    document.body.style.backgroundColor = hexNumber;
}

