export const drawRect = (detections,ctx) =>{
	detections.forEach( (prediction) => {
		const [x,y,height,width] = prediction['bbox'];
		const txt = prediction['class'];
		
		const color = 'green';
		ctx.strokeStyle = color;
		ctx.fillStyle = color;
		ctx.font = '20px Arial';
		
		ctx.beginPath();
		ctx.fillText(txt,x,y);
		ctx.rect(x,y,width,height);
		ctx.stroke();
	});

};