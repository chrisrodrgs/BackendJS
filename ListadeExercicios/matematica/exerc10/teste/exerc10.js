// 10. Escreva um programa que calcule o perímetro e a área de um triângulo utilizando as
// fórmulas: 
// perimetro = a + b + c
// area = (base * altura)/2
// triângulo equilatero: h= l Math.sqrt(3)/ 2     PARA DESCOBRIR ALTURA

document.addEventListener('DOMContentLoaded', function() {
    // Obter referências aos elementos
    const ladoEsquerdoInput = document.getElementById('ladoEsquerdo');
    const ladoDireitoInput = document.getElementById('ladoDireito');
    const baseInput = document.getElementById('base');
    const calculateBtn = document.getElementById('calculateBtn');
    const resultadosDiv = document.getElementById('resultados');
    
    // Elementos de erro
    const erroLadoEsquerdo = document.getElementById('erroLadoEsquerdo');
    const erroLadoDireito = document.getElementById('erroLadoDireito');
    const erroBase = document.getElementById('erroBase');
    
    // Função para validar um valor
    function validarValor(valor, elementoErro) {
        if (isNaN(valor) || valor <= 0) {
            elementoErro.style.display = 'block';
            return false;
        } else {
            elementoErro.style.display = 'none';
            return true;
        }
    }
    
    // Função para verificar se todos os campos são válidos
    function camposValidos() {
        const ladoEsquerdo = parseFloat(ladoEsquerdoInput.value);
        const ladoDireito = parseFloat(ladoDireitoInput.value);
        const base = parseFloat(baseInput.value);
        
        const validoEsquerdo = validarValor(ladoEsquerdo, erroLadoEsquerdo);
        const validoDireito = validarValor(ladoDireito, erroLadoDireito);
        const validoBase = validarValor(base, erroBase);
        
        return validoEsquerdo && validoDireito && validoBase;
    }
    
    // Adicionar eventos de validação em tempo real
    [ladoEsquerdoInput, ladoDireitoInput, baseInput].forEach(input => {
        input.addEventListener('input', function() {
            const valor = parseFloat(this.value);
            let elementoErro;
            
            if (this.id === 'ladoEsquerdo') elementoErro = erroLadoEsquerdo;
            else if (this.id === 'ladoDireito') elementoErro = erroLadoDireito;
            else elementoErro = erroBase;
            
            if (this.value === '') {
                elementoErro.style.display = 'none';
            } else {
                validarValor(valor, elementoErro);
            }
            
            // Habilitar/desabilitar botão
            calculateBtn.disabled = !camposValidos();
        });
    });
    
    // Evento do botão calcular
    calculateBtn.addEventListener('click', function() {
        if (!camposValidos()) {
            alert('Por favor, corrija os erros antes de calcular.');
            return;
        }
        
        // Obter os valores dos campos de entrada
        const valoresq = parseFloat(ladoEsquerdoInput.value);
        const valordir = parseFloat(ladoDireitoInput.value);
        const valorbase = parseFloat(baseInput.value);
        
        // Verificar se é um triângulo válido
        if (valoresq + valordir <= valorbase || 
            valoresq + valorbase <= valordir || 
            valordir + valorbase <= valoresq) {
            alert('Os valores informados não formam um triângulo válido!');
            resultadosDiv.style.display = 'none';
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
            
            // Exibir resultados
            resultadosDiv.innerHTML = `
                <h2>Resultados do Cálculo</h2>
                <div class="result-item">
                    <span class="result-label">Perímetro:</span> 
                    <span class="result-value">${perimetro.toFixed(2)}</span>
                </div>
                <div class="result-item">
                    <span class="result-label">Tipo do triângulo:</span> 
                    <span class="triangle-type ${tipo}">${tipo}</span>
                </div>
                <div class="result-item">
                    <span class="result-label">Altura relativa à base:</span> 
                    <span class="result-value">${altura.toFixed(2)}</span>
                </div>
                <div class="result-item">
                    <span class="result-label">Área do triângulo:</span> 
                    <span class="result-value">${area.toFixed(2)}</span>
                </div>
            `;
            resultadosDiv.style.display = 'block';
        }
    });
    
    // Inicialmente desabilitar o botão
    calculateBtn.disabled = true;
});