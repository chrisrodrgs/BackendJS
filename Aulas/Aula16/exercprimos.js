const numerosPrimos = [];

function ehPrimo(numero){
    if (numero <= 1) return false;

    for(let i = 2; i <= Math.sqrt(numero); i++){
        if (numero % i === 0)
            return false;
    }
    return true;
}
for(let i= 0; i <= 1000; i++){
    if(ehPrimo(i)){
        numerosPrimos.push(i)
    }
}

console.log('----- Números Primos -----')
console.log(numerosPrimos);