import React,{useEffect,useState,useReducer} from "react"
import Profiler from "react-dom"
import Webcam from "react-webcam";
import QrReader from 'react-qr-reader'
import Input  from './Components/Input';
import Button from  './Components/Button';
import Users  from './Components/Users'

import * as cocoSsd from "@tensorflow-models/coco-ssd";
import * as tf from "@tensorflow/tfjs";

export default function App() { 

  function reducer(state, action) {
	  switch (action.type) {
	    case 'increment':
	      return {count: state.count + 1};
	    case 'decrement':
	      return {count: state.count - 1};
	    case 'reset':
	      return init(action.payload);
	    default:
	      throw new Error();
	  }
}

	const [model, setModel] = useState();
	const webcamRef = React.useRef(null);  
	const [videoWidth, setVideoWidth] = useState(960);
	const [videoHeight, setVideoHeight] = useState(640);

	const videoConstraints = {
		height: 1080,
		width: 1920,
		facingMode: "environment",
	};

	async function loadModel() {
		try {
			const model = await cocoSsd.load();
			setModel(model);
			console.log(model);
			console.log("set loaded Model");
		} 
		catch (err) {
			console.log(err);
			console.log("failed load model");
		}
	}

	useEffect(() => {
		// console.log(cocoSsd);
		// console.log('loading model useEffect');
		// tf.ready().then(() => {
		// 	loadModel();
		// });
		const net =  cocoSsd.load();
		console.log(net);
	}, []);


	async function predictionFunction() {
		//Clear the canvas for each prediction
		var cnvs = document.getElementById("myCanvas");
		var ctx = cnvs.getContext("2d");
		ctx.clearRect(0,0, webcamRef.current.video.videoWidth,webcamRef.current.video.videoHeight);
		//Start prediction
		const predictions = await model.detect(document.getElementById("img"));
		if (predictions.length > 0) {
		console.log(predictions);
		for (let n = 0; n < predictions.length; n++) {
		console.log(n);
		if (predictions[n].score > 0.8) {
		//Threshold is 0.8 or 80%
		//Extracting the coordinate and the bounding box information
		let bboxLeft = predictions[n].bbox[0];
		let bboxTop = predictions[n].bbox[1];
		let bboxWidth = predictions[n].bbox[2];
		let bboxHeight = predictions[n].bbox[3] - bboxTop;
		console.log("bboxLeft: " + bboxLeft);
		console.log("bboxTop: " + bboxTop);
		console.log("bboxWidth: " + bboxWidth);
		console.log("bboxHeight: " + bboxHeight);
		//Drawing begin
		ctx.beginPath();
		ctx.font = "28px Arial";
		ctx.fillStyle = "red";
		ctx.fillText(
		predictions[n].class +": " + Math.round(parseFloat(predictions[n].score) * 100) +
		"%", bboxLeft,bboxTop);
		ctx.rect(bboxLeft, bboxTop, bboxWidth, bboxHeight);
		ctx.strokeStyle = "#FF0000";
		ctx.lineWidth = 3;
		ctx.stroke();
		console.log("detected");
		}
		}
		}
		//Rerun prediction by timeout
		setTimeout(() => predictionFunction(), 500);
	}


  return (  	
	    <div className="App">
	      <h2>Title 1</h2>
	      <p>
	      	Sample Paragraph
	      </p>
	     <div>
	      	<h2>Input text</h2>
	      	<Input placeholder="placehold" />
	      </div>
	      <div>
	      	{/*<h2>Button</h2>
	      	<Button title="Users title"/>*/}
	      	<button
				variant={"contained"}
				style={{
					color: "white",
					backgroundColor: "blueviolet",
					width: "50%",
					maxWidth: "250px",
				}}
				onClick={() => {
					predictionFunction();
					console.log('clicked');
				}}
			>Start Detect
		   </button>

		   <div style={{ position: "absolute", top: "1000px" }}>
			<Webcam
				audio={false}
				id="img"
				ref={webcamRef}
				screenshotQuality={1}
				screenshotFormat="image/jpeg"
				videoConstraints={videoConstraints}
				/>
			</div>
			<div style={{ position: "absolute", top: "400px", zIndex: "9999" }}>
				<canvas
				id="myCanvas"
				width={videoWidth}
				height={videoHeight}
				style={{ backgroundColor: "transparent" }}
				/>
			</div>
	      </div>
	    </div>

  );
}
