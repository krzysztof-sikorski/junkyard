.PHONY: build clean

# Compile CSS stylesheet
build:
	npx tailwindcss --input src/input.css --output public/css/combined.css --minify

# Remove compiled file
clean:
	rm --recursive --verbose node_modules
	rm -v public/css/combined.css
