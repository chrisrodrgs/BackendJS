// 10. Escreva um programa que calcule o perímetro e a área de um triângulo utilizando as
// fórmulas: 
// perimetro = a + b + c
// area = (base * altura)/2
//triângulo equilatero: h= l Math.sqrt(3)/ 2     PARA DESCOBRIR ALTURA

// Obter os dados com validação
let valoresq;
while (true) {
    valoresq = Number(prompt('Insira aqui o valor de lado esquerdo do triângulo:'));
    if (!isNaN(valoresq) && valoresq > 0) break;
    alert('O número deve ser positivo e maior que 0');
}

let valordir;
while (true) {
    valordir = Number(prompt('Insira aqui o valor de lado direito do triângulo:'));
    if (!isNaN(valordir) && valordir > 0) break;
    alert('O número deve ser positivo e maior que 0');
}

let valorbase;
while (true) {
    valorbase = Number(prompt('Insira aqui o valor de base do triângulo:'));
    if (!isNaN(valorbase) && valorbase > 0) break;
    alert('O número deve ser positivo e maior que 0');
}

// Verificar se é um triângulo válido
if (valoresq + valordir <= valorbase || 
    valoresq + valorbase <= valordir || 
    valordir + valorbase <= valoresq) {
    alert('Os valores informados não formam um triângulo válido!');
} else {
    // Calcular perímetro
    const perimetro = valoresq + valordir + valorbase;
    
    // Determinar tipo de triângulo e calcular altura/área
    let tipo = '';
    let area, altura;
    
    if (valoresq === valordir && valordir === valorbase) {
        // Triângulo equilátero
        tipo = 'equilátero';
        altura = (valoresq * Math.sqrt(3)) / 2;
        area = (valorbase * altura) / 2;
    } else {
        // Para outros tipos de triângulo, precisamos da altura
        // Usando a fórmula de Heron para calcular a área
        const s = perimetro / 2; // semiperímetro
        area = Math.sqrt(s * (s - valoresq) * (s - valordir) * (s - valorbase));
        
        // Calcular altura relativa à base
        altura = (2 * area) / valorbase;
        
        if (valoresq === valordir || valoresq === valorbase || valordir === valorbase) {
            tipo = 'isósceles';
        } else {
            tipo = 'escaleno';
        }
    }
    
    // Exibir resultados (assumindo que existe um elemento com id "resultados")
    const resultados = document.getElementById('resultados');
    if (resultados) {
        resultados.innerHTML = `
            O valor do perímetro do triângulo é: ${perimetro.toFixed(2)} <br>
            Este é um triângulo ${tipo} <br>
            Altura relativa à base: ${altura.toFixed(2)} <br>
            Área do triângulo: ${area.toFixed(2)}
        `;
    } else {
        console.log(`Perímetro: ${perimetro.toFixed(2)}`);
        console.log(`Tipo: ${tipo}`);
        console.log(`Altura: ${altura.toFixed(2)}`);
        console.log(`Área: ${area.toFixed(2)}`);
    }
}